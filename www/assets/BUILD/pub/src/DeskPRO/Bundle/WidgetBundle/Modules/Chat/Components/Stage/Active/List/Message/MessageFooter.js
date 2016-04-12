import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';
import classNames from 'classnames';
import { timeAgoFormatter } from '../../../../../../../Services/timeago';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class MessageFooter extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;

    const isUser = message.get('is_user');
    const date = message.get('date_created');
    const notDelivered = message.get('not_delivered');

    return (
      <div className="dpdesignportal-message-footer">
        {/* temporary disabled */ false &&
          <a href="#" className="dpdesignportal-message-footer-assets-link">
            <i className="fa fa-copy" /> Chat Assets (4)
          </a>
        }

        <div className={classNames({ right: isUser })}>
          {date &&
            <TimeAgo className="dpdesignportal-message-footer-timer"
                     formatter={timeAgoFormatter}
                     minPeriod={60000}
                     date={date} />
          }
          {notDelivered &&
            <span className="dpdesignportal-message-footer-not-delivered">
              <i className="fa fa-warning"/> {portalPhrases.get('portal.chat.asset_not_delivered')}
            </span>
          }
        </div>
      </div>
    );
  }
}
