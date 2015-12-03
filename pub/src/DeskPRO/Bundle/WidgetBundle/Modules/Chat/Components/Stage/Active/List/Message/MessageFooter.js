import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';
import classNames from 'classnames';

export class MessageFooter extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;

    const isUser = message.get('author_type') !== 'agent';
    const date = message.get('date_created');
    const timerClasses = classNames('dpdesignportal-message-footer-timer', {'right': isUser});

    return (
      <div className="dpdesignportal-message-footer">
        {/* temporary disabled */ false &&
          <a href="#" className="dpdesignportal-message-footer-assets-link">
            <i className="fa fa-copy"></i> Chat Assets (4)
          </a>
        }

        <TimeAgo className={timerClasses}
                 minPeriod={60000}
                 date={date} />
      </div>
    );
  }
}
