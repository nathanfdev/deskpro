import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';

class AuthorsAvatars extends React.PureComponent {
  static propTypes = {
    authors: PropTypes.array
  };

  static getInitials(author) {
    return author.first_name[0] + author.last_name[0];
  }

  renderAvatar = (author) => {
    if (author.avatar.url_pattern) {
      return (
        <Fragment>
          <span className="dp-po-post-avatars-image" aria-label={author.display_name} style={{ backgroundImage: `url('${author.avatar.url_pattern.replace('{{IMG_SIZE}}', 80)}')` }} />
        </Fragment>
      );
    }
    return (
      <Fragment>
        <span className="dp-po-post-avatars-name" aria-hidden="true">{AuthorsAvatars.getInitials(author)}</span>
        <span className="sr-only">{author.display_name}</span>
      </Fragment>
    );
  }

  render() {
    return (
      <div className="dp-po-post-avatars">
        <span className="sr-only"><FormattedMessage id="helpcenter.general.authors_list" /></span>
        <ul className="dp-po-post-avatars-list">
          {this.props.authors.map(author => (
            <li className="dp-po-post-avatars-item" key={author.id}>
              <a
                className="dp-po-post-avatars-link" data-toggle="tooltip"
                data-placement="bottom" title="" data-original-title={author.display_name}
              >
                {this.renderAvatar(author)}
              </a>
            </li>
          ))}
        </ul>
      </div>
    );
  }
}

export default AuthorsAvatars;
