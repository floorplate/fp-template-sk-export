<?php

namespace Floorplate\Anchors\Http\Controllers;

use Floorplate\Anchors\Support\AnchorFinder;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class EntryAnchorsController extends CpController
{
    public function show($entryId, AnchorFinder $finder)
    {
        $entry = Entry::find($entryId) ?? abort(404);

        $this->authorize('view', $entry);

        return ['data' => $finder->anchorsInEntry($entry)->all()];
    }
}
