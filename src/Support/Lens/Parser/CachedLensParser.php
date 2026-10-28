<?php

namespace Tanzar\Refract\Support\Lens\Parser;

use Illuminate\Support\Facades\DB;
use Override;

final class CachedLensParser extends LensParser
{

    #[Override]
    public function formatMetaData(): array
    {
        return $this->cached->meta_data;
    }

    #[Override]
    public function formatNodes(): array
    {
        $nodes = new JsonHandler();
        DB::table('refract_lens_paths')
            ->select([ 'path', 'path_value' ])
            ->where('cached_lens_id', $this->cached->id)
            ->lazyById(300, 'path')
            ->each(function (\stdClass $row) use (&$nodes) {
                $nodes->add(
                    $row->path,
                    (array) json_decode($row->path_value)
                );
            });
        return $nodes->toArray();
    }
    
}
