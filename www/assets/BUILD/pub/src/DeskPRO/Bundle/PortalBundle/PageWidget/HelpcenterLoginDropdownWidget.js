import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import map from 'lodash/map';

export class HelpcenterLoginDropdownWidget extends PageWidget {

  static addCaptchaIfNecessary() {
    portalHttp.sendGet(portalUrlGenerator.path('/captcha-html?action=login')).then((r) => {
      if (r.data.captcha_required) {
        // for now we are not displaying the captcha, and instead are just redirecting the user to login page
        window.location.href = portalUrlGenerator.path('/login');
      }
    });
  }

  init = () => {
    this.username = window.document.getElementById('login-form-username');
    if (this.username) {
      this.usernameLabel = this.username.parentElement;
    }
    this.password = window.document.getElementById('login-form-password');
    if (this.password) {
      this.passwordLabel = this.password.parentElement;
    }
    this.rememberMe = window.document.getElementById('login-form-remember-me');
    this.failedReason = window.document.getElementById('login-form-failed-reason');
    this.resetPassword = window.document.getElementById('login-form-reset-password');
    this.usersources = window.document.getElementById('login-form-usersources');
    this.userList = window.document.getElementById('login-form-userlist');
    this.userKey = window.document.getElementById('login-form-userkey');
  };

  onEmailBlur = () => {
    if (this.username && this.username.value) {
      this.resetPassword.setAttribute('href',
        `${portalUrlGenerator.path('/login/reset-password')}?email=${this.username.value}`
      );
    } else {
      this.resetPassword.setAttribute('href', portalUrlGenerator.path('/login/reset-password'));
    }
  };

  onResetFailed = () => {
    this.failedReason.innerHTML = '';
    this.usernameLabel.classList.remove('error');
    this.passwordLabel.classList.remove('error');
  };

  onKeyDown = (event) => {
    if (event.keyCode === 13) {
      this.onSubmit(event);
      return false;
    }
    return true;
  };

  onSubmit = (event) => {
    event.preventDefault();

    const loginUrl = portalUrlGenerator.path('/login/authenticate-password');

    this.onResetFailed();

    portalHttp.sendPost(
      loginUrl,
      {
        username:    this.username.value,
        password:    this.password.value,
        remember_me: this.rememberMe.checked,
        userkey:     this.userKey.value
      },
      {
        jsonPayload: false
      }
    ).then((r) => {
      if (r.data.success) {
        if ('redirect' in r.data) {
          window.location.href = r.data.redirect;
        } else {
          window.location.reload();
        }
      } else {
        const phrase = r.data.reason || 'helpcenter.account.login_invalid';
        this.failedReason.innerHTML = `<div class="message">${portalPhrases.get(phrase)}</div>`;
        this.usernameLabel.classList.add('error');
        this.passwordLabel.classList.add('error');
        this.username.focus();
        const userKey = this.userKey;
        const userList = this.userList;
        if (
          r.data.reason === 'helpcenter.account.multiple_matches'
          && r.data.identities
          && r.data.identities.length > 0
        ) {
          userList.innerHTML = '';
          r.data.identities.forEach((identity) => {
            const li = window.document.createElement('li');
            const span = window.document.createElement('span');
            span.innerText = `${identity.name}`;
            span.className = 'dp-po-multi-name';
            li.appendChild(span);
            const div = window.document.createElement('div');
            div.className = 'dp-po-multi-keys';
            const textArr = [];
            identity.keys.forEach((k) => {
              textArr.push(`${k.title}: ${k.value}`);
            });
            if (textArr.length > 0) {
              div.innerText = textArr.join(',');
              li.appendChild(div);
            }
            li.addEventListener(
              'click',
              (e) => {
                e.preventDefault();
                e.stopPropagation();
                userKey.value = identity.id;
                const kids = userList.getElementsByTagName('li');
                for (let i = 0; i < kids.length; i++) {
                  kids[i].className = kids[i].className.replace(' active', '');
                }
                li.className = `${li.className} active`;
              }
            );
            userList.appendChild(li);
          });
        }
        HelpcenterLoginDropdownWidget.addCaptchaIfNecessary();
      }
    });
  };

  renderWidget() {
    this.init();
    // we will have some input from the response on the usersources
    const usersources = window.DESKPRO_USERSOURCES;

    this.usersources.innerHtml = '';
    if (usersources.length > 0) {
      const div = window.document.createElement('div');
      div.className = 'dp-po-oauth';
      if (this.username) {
        const or = window.document.createElement('div');
        or.className = 'dp-po-divider';
        or.innerHTML = `<div class="dp-po-divider-text">${portalPhrases.get('helpcenter.general.or')}</div>`;
        div.appendChild(or);
      }
      map(usersources, (us) => {
        const source = window.document.createElement('a');
        source.href = `${portalUrlGenerator.path(`/login/authenticate/${us.id}?return=${window.location.href}`)}`;
        source.className = `btn btn-icon btn-lg btn-brand ${us.classes.join(' ')}`;
        source.innerHTML = `${us.icon !== null ? `<i class="dp-po-icon ${us.icon}"></i>` : ''}
              ${us.text}`;
        div.appendChild(source);
      });
      this.usersources.appendChild(div);
    }


    const loginSidebar = window.document.getElementById('login-sidebar');
    if (loginSidebar) {
      loginSidebar.addEventListener('submit', this.onSubmit);
    }
    if (this.username) {
      this.username.addEventListener('blur', this.onEmailBlur);
      this.username.addEventListener('change', this.onResetFailed);
      this.username.addEventListener('keydown', this.onKeyDown);
    }
    if (this.password) {
      this.password.addEventListener('change', this.onResetFailed);
      this.username.addEventListener('keydown', this.onKeyDown);
    }
  }
}
