<?php

namespace Devio\Taxonomies;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MergeTerms
{
    const COLUMNS = ['term_id', 'taxable_type', 'taxable_id'];

    protected Collection|array $from;
    protected int $to;

    public function __construct(Collection|array $from, Term|int $to)
    {
        $this->to = $to instanceof Term ? $to->getKey() : $to;

        $this->from = collect($from)->map(function ($term) {
            if ($term instanceof Term) return $term->getKey();
            elseif (is_numeric($term)) return $term;

            return null;
        })->filter(function ($term) {
            if ($term == $this->to) return false;

            return $term;
        });
    }

    public function merge()
    {
        DB::beginTransaction();

        try {
            $this->convertFroms();
            $this->deleteDuplicates();
            $this->deleteFrom();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }

    protected function deleteDuplicates()
    {
        foreach ($this->getDuplicates() as $duplicate) {
            DB::table('taxables')
                ->where(Arr::only((array)$duplicate, static::COLUMNS))
                ->limit($duplicate->times - 1)
                ->delete();
        }
    }

    protected function getDuplicates()
    {
        return DB::table('taxables')
            ->groupBy(static::COLUMNS)
            ->havingRaw('COUNT(*) > 1')
            ->get(array_merge(static::COLUMNS, [DB::raw('count(*) as times')]));
    }

    protected function deleteFrom()
    {
        Term::destroy($this->from);
    }

    protected function convertFroms()
    {
        \DB::table('taxables')->whereIn('term_id', $this->from)->update([
            'term_id' => $this->to
        ]);
    }
}