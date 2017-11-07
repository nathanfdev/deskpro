import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import { CalendarCellDropdown } from './CalendarCellDropdown';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { CalendarCellContentItem } from './CalendarCellContentItem';

export class CalendarCellContent extends React.Component {

  static propTypes = {
    dayDate:   PropTypes.object.isRequired,
    dateField: PropTypes.string.isRequired,
    elements:  PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { dropdownOpened: false };
  }

  onOpenAdditionalDropdown = event => {
    event.preventDefault();
    this.setState({ dropdownOpened: true });
  };

  onCloseAdditionalDropdown = () => {
    this.setState({ dropdownOpened: false });
  };

  renderItem = ([id, item]) => <CalendarCellContentItem key={item.get('id')} item={item} {...this.props} />;

  render() {
    const { elements = [], dateField, dayDate } = this.props;

    const fullList = elements.filter(item => dayDate.isSame(moment(item.get(dateField)), 'day'));
    const shortList = fullList.slice(0, 2);
    const additionalList = fullList.slice(2);

    return (
      <div>
        <ul>
          {shortList.entrySeq().map(this.renderItem)}
          {additionalList.count() > 0 &&
          <li>
            <a href="#" ref="button" className="dpwd-calendar-tasks-show-more" onClick={this.onOpenAdditionalDropdown}>
              + {additionalList.count()} tasks <i className="fa fa-sort" />
            </a>
          </li>
          }
        </ul>

        <Detached isOpen={this.state.dropdownOpened} positionTarget={this.refs.button} positionAt="left bottom+10">

          <ClickOut
            onClickOut={this.onCloseAdditionalDropdown}
            additionalNodes={['.calendar-task-card', '.assign-form']}
          >

            <CalendarCellDropdown dayDate={dayDate} {...this.props}>
              {additionalList.entrySeq().map(this.renderItem)}
            </CalendarCellDropdown>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
