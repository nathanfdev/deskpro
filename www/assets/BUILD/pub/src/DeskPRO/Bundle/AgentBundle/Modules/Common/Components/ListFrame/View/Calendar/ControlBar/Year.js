import PropTypes from 'prop-types';
import React from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { YearDropdown } from './YearDropdown';

export class Year extends React.Component {

  static propTypes = {
    date:     PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      dropdownOpened: false
    };
  }

  onOpenDropdown = () => {
    this.setState({
      dropdownOpened: true
    });
  };

  onCloseDropdown = () => {
    this.setState({
      dropdownOpened: false
    });
  };

  onChangeDate = date => {
    this.onCloseDropdown();
    this.props.onChange(date);
  };

  render() {
    const { date } = this.props;

    return (
      <div className="dpwd-calendar-controls-year">
        <span className="dpwd-calendar-controls-year-text">{date.format('YYYY')}</span>
        <span className="dpwd-calendar-controls-year-dropdown"
          ref="button"
          onClick={this.onOpenDropdown}
        >

          <i className="fa fa-caret-down" />
        </span>

        <Detached isOpen={this.state.dropdownOpened}
          positionTarget={this.refs.button}
          positionAt="left bottom"
        >

          <ClickOut onClickOut={this.onCloseDropdown}>
            <YearDropdown date={date}
              onChange={this.onChangeDate}
            />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
