<?php

namespace Floorplate\StillStreamAgent\Http\Controllers;

use Floorplate\StillStreamAgent\Support\ProjectAssets;
use Floorplate\StillStreamAgent\Support\StillStreamClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

/**
 * The StillStream pages for this site. Each one is the page floorplate.io
 * renders, filled with what Floorplate returns for this site; each action
 * is forwarded there and its answer shown here.
 */
class ProjectsController extends CpController
{
    public function __construct(Request $request, protected StillStreamClient $stillstream)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $data = $this->fetch('projects');

        return Inertia::render('stillstream::ProjectsIndex', [
            'projects' => array_map(fn (array $project) => $project + [
                'showUrl' => cp_route('stillstream-agent.projects.show', $project['id']),
                'destroyUrl' => cp_route('stillstream-agent.projects.destroy', $project['id']),
            ], $data['projects']),
            'accounts' => $data['accounts'],
            'templates' => $data['templates'],
            'storeUrl' => cp_route('stillstream-agent.projects.store'),
            'can' => ['create' => (bool) User::current()->can('create stillstream projects')],
        ]);
    }

    public function store(Request $request)
    {
        $result = $this->stillstream->send(User::current(), 'POST', 'projects', [
            'name' => $request->input('name'),
            'template_id' => $request->input('template_id'),
        ]);

        if ($result['status'] !== 201) {
            return $this->failed($result);
        }

        return redirect()->to(cp_route('stillstream-agent.projects.show', $result['data']['id']));
    }

    public function show(int $project, ProjectAssets $assets)
    {
        $user = User::current();
        $data = $this->fetch("projects/{$project}");

        $assets->register($data['generated'] ?? []);
        unset($data['generated']);

        return Inertia::render('stillstream::ProjectShow', $data + [
            'urls' => [
                'index' => cp_route('stillstream-agent.projects.index'),
                'editor' => cp_route('stillstream-agent.projects.editor', $project),
                'update' => cp_route('stillstream-agent.projects.update', $project),
                'upload' => cp_route('stillstream-agent.projects.upload', $project),
                'clip' => cp_route('stillstream-agent.projects.clip', $project),
                'audio' => cp_route('stillstream-agent.projects.audio', $project),
                'render' => cp_route('stillstream-agent.projects.render', $project),
            ],
            'can' => [
                'edit' => (bool) $user->can('create stillstream projects'),
                'generate' => (bool) $user->can('generate stillstream media'),
                'render' => (bool) $user->can('render stillstream videos'),
            ],
        ]);
    }

    public function editor(int $project)
    {
        return Inertia::render('stillstream::ProjectEditor', $this->fetch("projects/{$project}/editor") + [
            'urls' => [
                'workspace' => cp_route('stillstream-agent.projects.show', $project),
                'document' => cp_route('stillstream-agent.projects.document', $project),
                'fonts' => cp_route('stillstream-agent.projects.fonts', $project),
            ],
            'can' => ['edit' => (bool) User::current()->can('create stillstream projects')],
        ]);
    }

    public function saveDocument(Request $request, int $project)
    {
        return $this->forward('PUT', "projects/{$project}/document", ['document' => $request->input('document')]);
    }

    public function update(Request $request, int $project)
    {
        return $this->forward('PATCH', "projects/{$project}", $request->only(['name', 'texts']), 'Saved.');
    }

    public function destroy(int $project)
    {
        $result = $this->stillstream->send(User::current(), 'DELETE', "projects/{$project}");

        return $result['status'] === 200
            ? redirect()->to(cp_route('stillstream-agent.projects.index'))->with('success', 'Project deleted.')
            : $this->failed($result);
    }

    /** The photo becomes an asset here first; Floorplate is then told where it is. */
    public function upload(Request $request, int $project, ProjectAssets $assets)
    {
        $request->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200']]);

        $uuid = $this->fetch("projects/{$project}")['project']['uuid'];
        $asset = $assets->upload($uuid, $request->file('photo'));

        return $this->forward('POST', "projects/{$project}/photos", [
            'path' => $asset->path(),
            // Used when this site is not on a Floorplate assets bucket.
            'url' => $asset->absoluteUrl(),
            'width' => $asset->width(),
            'height' => $asset->height(),
            'size' => $asset->size(),
            'mime' => $asset->mimeType(),
        ]);
    }

    /** A font is an asset here too, under the project's fonts folder. */
    public function font(Request $request, int $project, ProjectAssets $assets)
    {
        $data = $request->validate([
            'font' => ['required', 'file', 'extensions:ttf,otf,woff,woff2', 'max:5120'],
            'family' => ['required', 'string', 'max:80'],
        ]);

        $uuid = $this->fetch("projects/{$project}")['project']['uuid'];
        $asset = $assets->upload($uuid, $request->file('font'), 'fonts');

        return $this->forward('POST', "projects/{$project}/fonts", [
            'path' => $asset->path(),
            // Used when this site is not on a Floorplate assets bucket.
            'url' => $asset->absoluteUrl(),
            'family' => $data['family'],
            'size' => $asset->size(),
            'mime' => $asset->mimeType(),
        ]);
    }

    public function clip(Request $request, int $project)
    {
        return $this->forward('POST', "projects/{$project}/clips", $request->only(['slot', 'photo', 'move', 'strength', 'prompt']));
    }

    public function audio(Request $request, int $project)
    {
        return $this->forward('POST', "projects/{$project}/audio", $request->only(['slot', 'preset']));
    }

    public function render(int $project)
    {
        return $this->forward('POST', "projects/{$project}/renders");
    }

    /**
     * Data for a page. A page that cannot be loaded is a 404 or an error
     * page, not a half-drawn one.
     *
     * @return array<string, mixed>
     */
    protected function fetch(string $path): array
    {
        $result = $this->stillstream->send(User::current(), 'GET', $path);

        abort_if($result['status'] === 404, 404);
        abort_unless($result['status'] === 200, 503, $result['data']['message'] ?? 'StillStream is unavailable.');

        return $result['data'];
    }

    /** @param  array<string, mixed>  $data */
    protected function forward(string $method, string $path, array $data = [], ?string $success = null): RedirectResponse
    {
        $result = $this->stillstream->send(User::current(), $method, $path, $data);

        if ($result['status'] >= 300) {
            return $this->failed($result);
        }

        return $success ? back()->with('success', $success) : back();
    }

    /** @param  array{status: int, data: array<string, mixed>}  $result */
    protected function failed(array $result): RedirectResponse
    {
        abort_if($result['status'] === 404, 404);

        // Floorplate validates the same fields the form shows.
        if (isset($result['data']['errors']) && is_array($result['data']['errors'])) {
            return back()->withErrors($result['data']['errors']);
        }

        return back()->with('error', $result['data']['message'] ?? 'Something went wrong. Try again in a moment.');
    }
}
