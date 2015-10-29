import React, {Component, PropTypes} from 'react';

export class TypesCollection extends Component {

  static propTypes = {
    options: PropTypes.object.isRequired
  };

  renderListItem(item) {
    return (
      <li>
        <div className="dpw--popup-item-person">
          <span className="dpw-popup-item-collection-name">
            {item.title}
          </span>
        </div>
      </li>
    );
  }

  render() {
    const {options} = this.props;
    console.log('Types options: ', options.toJS());
    return (
      <ul>
        {options.toJS().map(item => this.renderListItem(item))}
      </ul>
    );
  }
}