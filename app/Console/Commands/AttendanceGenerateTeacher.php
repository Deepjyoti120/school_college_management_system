<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttendanceGenerateTeacher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:attendance-generate-teacher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//         CREATE OR REPLACE FUNCTION public.generate_teacher_daily_attendance()
//             RETURNS void
//             LANGUAGE plpgsql
//             AS $function$
//             BEGIN
//                 INSERT INTO attendances (user_id, date, status, created_at, updated_at)
//                 SELECT 
//                     u.id,
//                     CURRENT_DATE,
// --                     CASE 
// --                         WHEN EXISTS (
// --                             SELECT 1 
// --                             FROM leaves l
// --                             WHERE l.user_id = u.id
// --                               AND l.status = 'approved'
// --                               AND CURRENT_DATE BETWEEN l.start_date AND l.end_date
// --                         )
// --                         THEN 'leave'
// --                         ELSE 
//                         'absent',
// --                     END AS status,
//                     NOW(),
//                     NOW()
//                 FROM users u
//                 WHERE NOT EXISTS (
//                     SELECT 1 FROM attendances a
//                     WHERE a.user_id = u.id 
//                       AND a.date = CURRENT_DATE
//                 );
//             END;
//             $function$;
       DB::statement('SELECT generate_teacher_daily_attendance()');
    }
}
