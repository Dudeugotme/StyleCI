<?php

/*
 * This file is part of StyleCI.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 * (c) Joseph Cohen <joseph.cohen@dinkbit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\StyleCI\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use StyleCI\StyleCI\Commands\AnalyseCommitCommand;
use StyleCI\StyleCI\GetCommitTrait;
use StyleCI\StyleCI\GitHub\Branches;
use StyleCI\StyleCI\GitHub\Repos;
use StyleCI\StyleCI\Models\Repo;

/**
 * This is the repo controller class.
 *
 * @author Graham Campbell <graham@mineuk.com>
 */
class RepoController extends AbstractController
{
    use GetCommitTrait;

    /**
     * Create a new account controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'handleShow']);
    }

    /**
     * Handles the request to list the repos.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     * @param \StyleCI\StyleCI\GitHub\Repos    $repos
     *
     * @return \Illuminate\View\View
     */
    public function handleList(Guard $auth, Repos $repos)
    {
        $repos = Repo::whereIn('id', array_keys($repos->get($auth->user())))->orderBy('name', 'asc')->get();

        $commits = new Collection();

        foreach ($repos as $repo) {
            $commits->put($repo->id, $repo->commits()->where('ref', 'refs/heads/master')->orderBy('created_at', 'desc')->first());
        }

        return View::make('repos', compact('repos', 'commits'));
    }

    /**
     * Handles the request to show a repo.
     *
     * @param \StyleCI\StyleCI\Models\Repo $repo
     *
     * @return \Illuminate\View\View
     */
    public function handleShow(Repo $repo)
    {
        $commits = $repo->commits()->where('ref', 'refs/heads/master')->orderBy('created_at', 'desc')->paginate(50);

        return View::make('repo', compact('repo', 'commits'));
    }

    /**
     * Handles the request to analyse a repo.
     *
     * @param \Illuminate\Http\Request         $request
     * @param \StyleCI\StyleCI\GitHub\Branches $branches
     * @param \StyleCI\StyleCI\Models\Repo     $repo
     *
     * @return \Illuminate\Http\Response
     */
    public function handleAnalyse(Request $request, Repo $repo, Branches $branches)
    {
        $branches = $branches->get($repo);

        foreach ($branches as $branch) {
            if ($branch['name'] !== 'master') {
                continue;
            }

            $commit = static::getCommit($branch['name'], $repo->id, $branch['commit']);
            $this->dispatch(new AnalyseCommitCommand($commit));
            break;
        }

        if ($request->ajax()) {
            return new JsonResponse(['queued' => true]);
        }

        return Redirect::route('repo_path', $repo->id);
    }
}
