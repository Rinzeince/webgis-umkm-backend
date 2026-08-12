<?php

namespace App\Services;

use App\Jobs\RunKMeansAnalysisJob;
use App\Models\Analisis;

class AnalysisService
{
    /**
     * Trigger a new K-Means Analysis job.
     * Creates a new Analisis record with status_job = 'dalam_antrean' and dispatches job synchronously.
     * If the job fails, the record is deleted to prevent skipped queue numbers.
     */
    public function triggerAnalysis(): Analisis
    {
        $analisis = Analisis::create([
            'status_job' => 'dalam_antrean',
        ]);

        RunKMeansAnalysisJob::dispatchSync($analisis->id_analisis);

        $analisis->refresh();

        // If analysis failed, delete the record completely to prevent skipped queue numbers
        if ($analisis->status_job === 'gagal') {
            $errorLog = $analisis->error_log;
            $analisis->hasilCluster()->delete();
            $analisis->centroids()->delete();
            $analisis->delete();

            // Return a transient object for error display purposes
            $transient = new Analisis();
            $transient->status_job = 'gagal';
            $transient->error_log = $errorLog;
            $transient->id_analisis = null;

            return $transient;
        }

        return $analisis;
    }
}
