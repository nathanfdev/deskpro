import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import $ from "jquery"

export default class LoginPage extends PageWidget {
  renderWidget() {
    const $login_page = this.$element;
    const $email_input = $login_page.find('input[name="username"]');
    const $password_input = $login_page.find('input[name="password"]');

    if (!$email_input.val()) {
      $email_input.focus();
    } else {
      $password_input.focus();
    }
  }
}
