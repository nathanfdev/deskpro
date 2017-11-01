import PropTypes from 'prop-types';
import React from 'react';

export class CalendarCellDropdown extends React.Component {

  static propTypes = {
    additionalPrefix: PropTypes.string,
    dayDate:          PropTypes.object.isRequired,
    children:         PropTypes.node
  };

  render() {
    const { additionalPrefix, dayDate, children } = this.props;

    return (
      <div className="dpw--popup-main dpw--popup-main-pointer dpw--popup-main-pointer-top-left calendar-popup">
        <div className="dpw--popup-header">{additionalPrefix} {dayDate.format('MMMM Do')}</div>

        <div className="dpw--popup-content">
          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-full">
              <div className="dpwd-calendar-tasks dpwd-calendar-tasks-flat">
                <ul>
                  {children}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
