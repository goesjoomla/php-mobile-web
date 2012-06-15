<h3>Login Form</h3>
<form action="/" method="post" class="login-form" onsubmit="return false;">
	<fieldset class="collapse">
		<legend>Login Form</legend>
		<p>
			<label for="login-form-username">Username</label>
			<input id="login-form-username" type="text" name="username" value="" title="Input username" />
		</p>
		<p>
			<label for="login-form-password">Password</label>
			<input id="login-form-password" type="password" name="password" value="" title="Input password" />
		</p>
		<p>
			<span class="label">
				<input type="submit" name="submit" value="Login" class="button" title="Click to login" />
			</span>
			<span class="field">
				<input id="login-form-remember" type="checkbox" name="remember" value="1" title="Stay signed in" />
				<label for="login-form-remember">Remember Me</label>
			</span>
		</p>
		<ul class="no-bullet">
			<li><a href="#/forgot-password" title="Password Reminder">Lost Password?</a></li>
			<li><a href="#/register" title="Register New Account">Register</a></li>
		</ul>
	</fieldset>
</form>