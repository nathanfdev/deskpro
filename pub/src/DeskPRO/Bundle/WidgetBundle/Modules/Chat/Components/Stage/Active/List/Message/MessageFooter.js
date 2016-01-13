import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';
import classNames from 'classnames';
import { timeAgoForamtter } from '../../../../../../../Services/timeago';

export class MessageFooter extends React.Component {

  static propTypes = {
    authorType: PropTypes.string,
    message: PropTypes.object
  };

  render() {
    const { message, authorType } = this.props;

    const isUser = authorType !== 'agent';
    const date = message.get('date_created');
    const notDelivered = message.get('not_delivered');

    return (
      <div className="dpdesignportal-message-footer">
        {/* temporary disabled */ false &&
          <a href="#" className="dpdesignportal-message-footer-assets-link">
            <i className="fa fa-copy"></i> Chat Assets (4)
          </a>
        }

        <div className={classNames({'right': isUser})}>
          {date &&
            <TimeAgo className="dpdesignportal-message-footer-timer"
                     formatter={timeAgoForamtter}
                     minPeriod={60000}
                     date={date}/>
          }
          {notDelivered &&
            <span className="dpdesignportal-message-footer-not-delivered">
              <i className="fa fa-warning"/> Not delivered
            </span>
          }
        </div>
      </div>
    );
  }
}
