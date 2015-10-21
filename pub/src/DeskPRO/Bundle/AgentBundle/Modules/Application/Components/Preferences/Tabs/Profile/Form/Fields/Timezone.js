import React, { PropTypes } from 'react';

export class Timezone extends React.Component {

  static propTypes = {
    timezones: PropTypes.object.isRequired,
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  render() {
    const { timezones } = this.props;

    return (
      <div className="bucket-column">
        <a href="#" className="select">Europe/London (0 GMT) <i className="fa fa-caret-down"></i></a>
        <select>
          {timezones.map(timezone => <option key={timezone.get('id')} value={timezone.get('id')}>{timezone.get('title')}</option>)}
        </select>
      </div>
    );
  }
}
