import React, {Component, PropTypes} from 'react';

export class DropdownList extends Component {
  static propTypes = {
    title: PropTypes.string.isRequired,
    items: PropTypes.object.isRequired,
    option: PropTypes.string.isRequired
  };

  renderOptions() {
    const { items, option } = this.props;
    return items.map(
      (item, index) => <option key={index} value={item.get('id')}>{item.get(option)}</option>);
  }

  render() {
    const { title } = this.props;
    return (
      <div className="dpw--popup-content-left even">
        <h2 className="dpw--popup-item-section-title">{title}</h2>

        <div className="dpw--popup-form-container">
          <select>
            {this.renderOptions()}
          </select>
        </div>
      </div>
    );
  }
}