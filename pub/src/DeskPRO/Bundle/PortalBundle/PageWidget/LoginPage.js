import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';

export default class LoginPage extends PageWidget {

  renderWidget() {
    const $loginPage = this.$element;
    const $emailInput = $loginPage.find('input[name="username"]');
    const $passwordInput = $loginPage.find('input[name="password"]');

    if (!$emailInput.val()) {
      $emailInput.focus();
    } else {
      $passwordInput.focus();
    }
  }
}
