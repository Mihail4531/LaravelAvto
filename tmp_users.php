$rows = DB::table('users')->get(['id','name','login','email']);
foreach ($rows as $r) { echo $r->id." | ".$r->login." | ".$r->email."\n"; }
