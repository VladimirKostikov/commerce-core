<?php

namespace App\Http\Controllers;

use App\Contracts\TestRunLogStoreInterface;
use App\Contracts\TestSuiteRunnerInterface;
use App\Dto\TestRunResult;
use App\Http\Requests\TestRunRequest;
use App\Contracts\TestCatalogSyncInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class TestRunController extends Controller
{
    public function run(
        TestRunRequest $request,
        TestSuiteRunnerInterface $runner,
        TestRunLogStoreInterface $logs,
        TestCatalogSyncInterface $sync,
    ): Response {
        set_time_limit((int) config('test_runs.timeout', 300));
        $sync->sync();

        $case = $this->caseId($request);

        if ($case !== '') {
            $result = $runner->runCase($case);
            $logs->put($case, $result->output);

            return $this->report($result);
        }

        $suite = $request->string('suite')->toString();
        $result = $runner->run($suite);
        $logs->put('suite:'.$suite, $result->output);

        return $this->report($result);
    }

    public function log(Request $request, TestRunLogStoreInterface $logs): Response
    {
        $id = $this->caseId($request);
        $output = $id === '' ? null : $logs->get($id);

        return response($output ?? 'пусто', 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function caseId(Request $request): string
    {
        if ($request->filled('case')) {
            return $request->string('case')->toString();
        }

        if ($request->filled('class') && $request->filled('method')) {
            return $request->string('class')->toString().'::'.$request->string('method')->toString();
        }

        return '';
    }

    private function report(TestRunResult $result): Response
    {
        $mark = $result->ok ? 'ok' : 'fail';

        return response(
            $result->suite.'  '.$mark."\n\n".$result->output,
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }
}
