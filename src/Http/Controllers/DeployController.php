<?php

/*
 * This file is part of the ziorye/ddeployer.
 *
 * (c) ziorye <ziorye@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Ziorye\DDeployer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class DeployController extends BaseController
{
    public function deploy(Request $request)
    {
        set_time_limit(config('ddeployer.set_time_limit'));

        // ==============================
        // 1. pre check
        // ==============================
        if (app()->isLocal()) {
            return response()->json('application is in local environment. Ignoring.', 403);
        }
        $localToken = config('ddeployer.secret_token');
        if (empty($localToken)) {
            return response()->json('No secret_token found. Ignoring.', 403);
        }
        // Github
        $githubXHeader = $request->header('X-Hub-Signature');
        if (! empty($githubXHeader)) {
            $content = $request->getContent();
            $localHash = 'sha1=' . hash_hmac('sha1', $content, $localToken, false);
            if (! hash_equals($githubXHeader, $localHash)) {
                return response()->json('X-Hub-Signature validation failed.', 403);
            }
        }
        // Gitlab
        $gitlabXHeader = $request->header('X-Gitlab-Token');
        if (! empty($gitlabXHeader)) {
            if ($localToken !== $gitlabXHeader) {
                return response()->json('X-Gitlab-Token validation failed.', 403);
            }
        }
        if (empty($githubXHeader) && empty($gitlabXHeader)) {
            return response()->json('Missing X header.', 403);
        }

        // ==============================
        // 2. check branch
        // ==============================
        $branch = $request->get('branch', config('ddeployer.default_branch_to_be_pull'));
        if ($request->get('ref') !== 'refs/heads/' . $branch) {
            return response()->json('the ref in payload [' . $request->get('ref') . '] does not match [refs/heads/' . $branch . '], No need to do any thing', 403);
        }

        if (config('ddeployer.extra_check')) {
            // ==============================
            // 3. extra check
            // Check if [the branch you are on] matches [the branch you specified in the request parameter]
            // ==============================
            $cmd = 'git rev-parse --abbrev-ref HEAD';
            $output = $this->runCmd($cmd);
            if ($output !== $branch) {
                return response()->json('the name of the branch you are on [' . $output . '] does not match the branch you specified in the request parameter [' . $branch . ']', 403);
            }
        }

        // ==============================
        // 4. analyze commits
        // ==============================
        $commits = $request->get('commits');
        if (empty($commits)) {
            return response()->json('Empty commits.', 403);
        }
        $commandLists = str_replace('{$branch}', $branch, config('ddeployer.commands.before'));
        if (! empty(config('ddeployer.commands.condition'))) {
            foreach ($commits as $commit) {
                foreach (['added', 'modified', 'removed'] as $type) {
                    foreach ($commit[$type] as $item) {
                        foreach (config('ddeployer.commands.condition') as $files => $commands) {
                            if (Str::contains($item, explode(',', $files))) {
                                $commandLists = array_merge($commandLists, $commands);
                            }
                        }
                    }
                }
            }
        }
        $commandLists = array_unique(array_merge($commandLists, config('ddeployer.commands.after')));
        foreach (['php', 'git', 'composer'] as $command) {
            $commandLists = str_replace($command . ' ', config('ddeployer.' . $command . '_bin_path') . ' ', $commandLists);
        }

        // ==============================
        // 5. execute commands
        // ==============================
        if (app()->runningInConsole() && app()->runningUnitTests()) {
            return $commandLists;
        } else {
            $output = '';
            foreach ($commandLists as $cmd) {
                $output .= $this->runCmd($cmd) . PHP_EOL;
            }

            return $output;
        }
    }

    private function runCmd(string $cmd)
    {
        $process = Process::fromShellCommandline($cmd, base_path());
        $process->run();
        if (! $process->isSuccessful()) {
            Log::error($process->getErrorOutput());

            return '[' . $cmd . '] execute failed';
        }

        return trim($process->getOutput());
    }
}
