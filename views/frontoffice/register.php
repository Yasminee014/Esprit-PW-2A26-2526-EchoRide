<form action="../../controllers/UserController.php?action=register" method="POST" id="registerForm">

<input name="firstname" placeholder="Prénom">
<input name="lastname" placeholder="Nom">
<input name="email" placeholder="Email">
<input name="password" type="password">
<input name="phone" placeholder="Téléphone">

<select name="role">
  <option value="passenger">Passager</option>
  <option value="driver">Conducteur</option>
</select>

<button>S'inscrire</button>
</form>

<script src="<?= BASE_URL ?>views/frontoffice/js/register.validation.js"></script>