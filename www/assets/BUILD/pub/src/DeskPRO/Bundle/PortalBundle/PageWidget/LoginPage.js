import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class LoginPage extends PageWidget {

  renderWidget() {
    const $loginPage = this.$element;
    const $emailInput = $loginPage.find('input[name="username"]');
    const $passwordInput = $loginPage.find('input[name="password"]');
    const $multiList = $loginPage.find('#user-multi-list');
    const $userkey = $loginPage.find('input[name="userkey"]');

    if ($multiList && $userkey) {
      const $lis = $multiList.find('>li');
      $lis.each((_, li) => {
        $(li).click((e) => {
          e.preventDefault();
          e.stopPropagation();
          $lis.removeClass('active');
          $(li).addClass('active');
          $userkey.val($(li).data('person'));
        });
      });
    }
    if (!$emailInput.val()) {
      $emailInput.focus();
    } else {
      $passwordInput.focus();
    }
  }
}
