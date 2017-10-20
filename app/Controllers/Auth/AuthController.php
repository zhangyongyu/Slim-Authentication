<?php


namespace App\Controllers\Auth;

use Slim\Views\Twig as View;
use App\Controllers\Controller;
use App\Models\User;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{
	
	public function getSignIn($request, $response)
	{
		return $this->view->render($response, 'auth/signin.twig');
	}


	public function postSignIn($request, $response)
	{
		$auth = $this->auth->attempt(
			$request->getParam('email'),
			$request->getParam('password')
		);

		if (!$auth) {
			$this->flash->addMessage('error','Wrong email or password.');
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}

		return $response->withRedirect($this->router->pathFor('home'));


	}


	public function getSignUp($request, $response)
	{
		return $this->view->render($response, 'auth/signup.twig');
	}


	public function postSignUp($request, $response)
	{
		$validation = $this->validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
			'name' => v::notEmpty()->alpha(),
			'password' => v::noWhitespace()->notEmpty(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.signup'));
		}

		$user = User::create([
			'email' => $request->getParam('email'),
			'name' => $request->getParam('name'),
			'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
		]);

		$this->flash->addMessage('info','You have been signed up!');

		$this->auth->attempt($user->email, $request->getParam('password'));

		return $response->withRedirect($this->router->pathFor('home'));
	}


	public function getSignOut($request, $response)
	{
		$this->auth->logout();

		return $response->withRedirect($this->router->pathFor('home'));
	}


	public function getChangePassword($request, $response)
	{
		return $this->view->render($response, 'auth/changepassword.twig');
	}

	public function postChangePassword($request, $response)
	{
		$validation = $this->validator->validate($request, [
			'password_old' => v::matchesPassword($this->auth->user()->password),
			'password' => v::noWhitespace()->notEmpty()->length(5)
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.changepassword'));
		}

		var_dump($request->getParam('password'));

		$this->auth->user()->setPassword($request->getParam('password'));

		$this->flash->addMessage('info', 'Your password was changed.');

		return $response->withRedirect($this->router->pathFor('home'));
	}


}