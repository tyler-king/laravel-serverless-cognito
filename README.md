Cognito vapor required also adding an A record of {login}.domain.com to the www.domain.com record created by vapor

To use the middleware, use the 'api.cognito' group

All requests should specify Accept: appliction/json.

Steps:

- Run migrations
- in `App\Models\User`
  - set `use Cognito`
  - add to `protected $casts = [ 'scopes'=> 'array' ]`
  - add to `protected $fillable = [ 'sub', 'scopes' ]`

- in `Database\Factories\UserFactory::definition`
  - add `'scopes' => []`
  - add `'sub' => $this->faker->uuid` 
