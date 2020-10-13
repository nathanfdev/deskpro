import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { getErrorsByErrorPath } from 'DeskPRO/Component/Form/FormErrors';
import { FormattedMessage } from 'react-intl';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

class CommentForm extends React.Component {
  static propTypes = {
    onSubmit: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      comment: undefined,
      name:    '',
      email:   '',
      errors:  {},
      flashes: {},
      loading: false,
    };
  }

  onSubmit = (e) => {
    e.preventDefault();
    if (this.state.comment || !this.state.loading) {
      this.setState({
        loading: true,
        errors:  {}
      });
      const params = {
        comment: {
          content_real:   this.state.comment,
          name:           this.state.name,
          email:          this.state.email,
          _dp_csrf_token: window.dp_get_csrf_token()
        }
      };
      if (this.recaptcha) {
        params['g-recaptcha-response'] = window.document.getElementById('g-recaptcha-response').value;
      }
      this.props.onSubmit(params).then(() => {
        this.setState({
          comment: '',
          name:    '',
          email:   '',
          loading: false
        });
      }).catch((response) => {
        let errors = [];
        if (response.data) {
          errors = response.data.data.errors;
          if (response.data.data.general_errors) {
            errors.general_errors = response.data.data.general_errors;
          }
        }
        window.dp_refresh_csrf_token();
        this.setState({
          loading: false,
          errors
        });
      });
    }
  };

  getPersonFields = () => {
    if (!window.loggedIn) {
      return (
        <div>
          <div className="column-full">
            <div className={classNames('bucket', { error: this.hasError('name') })}>
              <label className="title title required" htmlFor="comment_name">
              Your Name *
            </label>
              <input
                id="comment_name"
                name="comment[name]"
                required="required"
                type="text"
                value={this.state.name}
                onChange={this.updateName}
              />
              {this.getError('name')}
            </div>
          </div>
          <div className="column-full">
            <div className={classNames('bucket', { error: this.hasError('email') })}>
              <label className="title title required" htmlFor="comment_email">
              Email *
            </label>
              <input
                id="comment_email"
                name="comment[email]"
                required="required"
                type="email"
                value={this.state.email}
                onChange={this.updateEmail}
              />
              {this.getError('email')}
            </div>
          </div>
        </div>
      );
    }
    return null;
  };

  getCaptchaField = () => {
    const captcha = JSON.parse(window.commentsCaptcha);
    if (!captcha) {
      return null;
    }
    if (captcha.type === 'recaptcha') {
      return (
        <div className="column-full">
          <div className={classNames('bucket', { error: this.hasError('captcha') })}>
            <div className="g-recaptcha" data-sitekey={captcha.key} ref={(c) => { this.recaptcha = c; }} />
          </div>
        </div>
      );
    }
    return null;
  };

  getError = (field) => {
    const errors = getErrorsByErrorPath(this.state.errors, ['form', 'children', field, 'errors']);
    if (!errors.length) {
      return null;
    }
    const error = errors.slice(-1)[0];
    return (
      <div className="error-large no-hover">{error}</div>
    );
  };

  getGeneralError = () => {
    if (this.state.errors.general_errors) {
      return this.state.errors.general_errors.map(error => (
        <div className="dp-po-message-bar-error error-large no-hover">{error}</div>
      ));
    }
    return null;
  }

  hasError = field => getErrorsByErrorPath(this.state.errors, ['form', 'children', field, 'errors']).length > 0;

  updateComment = (e) => {
    this.setState({
      comment: e.target.value
    });
  };

  updateName = (e) => {
    this.setState({
      name: e.target.value
    });
  };

  updateEmail = (e) => {
    this.setState({
      email: e.target.value
    });
  };

  sanitize = (string) => {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#x27;',
      '/': '&#x2F;',
    };
    const reg = /[&<>"'/]/ig;
    return string.replace(reg, match => (map[match]));
  }

  render() {
    if (!window.pageCommentForm) {
      return null;
    }
    if (!window.loggedIn) {
      return (
        <div>
          <p className="dp-po-comment-subtitle">
            <FormattedMessage id="helpcenter.general.comment_login_first" />
          </p>

        </div>
      );
    }

    let avatar;
    if (window.user_avatar) {
      avatar = <span className="dp-po-avatar-image" aria-hidden="true" style={{ backgroundImage: `url(${window.user_avatar})` }} />;
    } else {
      avatar = <span className="dp-po-avatar-name">{window.user_initials}</span>;
    }

    return (
      <div className="dp-po-block">
        <div className="dp-po-comments-add">
          <div className="dp-po-avatar">
            {avatar}
            <strong>{window.user_name}</strong>
          </div>

          {this.getGeneralError()}
          <form action="" className="dp-po-form" method="post">
            <div className="form-group">
              <label className="title title required" htmlFor="comment_content_real">
                <FormattedMessage id="helpcenter.general.your_comment_label" /> *
              </label>
              <textarea
                className="form-control"
                id="comment_content_real"
                name="comment[content_real]"
                required="required"
                value={this.state.comment}
                onChange={this.updateComment}
              />
              {this.getError('content_real')}
            </div>
            <div className="row align-items-center">
              <div className="col-sm-2">
                <button
                  type="submit"
                  className="btn btn-primary"
                  onClick={this.onSubmit}
                  disabled={this.state.loading}
                  style={{ whiteSpace: 'nowrap' }}
                >
                  <FormattedMessage id="helpcenter.general.comment_btn_save" />&nbsp;
                  {this.state.loading ? <figure className="dp-po-icon"><FontAwesomeIcon icon={['fas', 'spinner']} pulse /></figure> : '' }
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    );
  }
}
export default CommentForm;
