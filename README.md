Edit config/auth.php to include

Add a provider:
'cognito' => [
'driver' => 'eloquent',
'model' => TKing\ServerlessCognito\Cognito::class,
]

Add a guard:
'cognito' => [
'driver' => 'cognito',
'provider' => 'cognito',
'hash' => false,
],

Then in your middleware set auth:cognito

cognito vapor required also adding an A record of .domain.com to the www.domain.com record created by vapor
