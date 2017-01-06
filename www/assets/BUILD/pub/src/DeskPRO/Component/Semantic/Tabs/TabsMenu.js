import React, { PropTypes } from 'react';

class TabsMenu extends React.Component {

  static propTypes = {
    children: PropTypes.oneOfType([
      PropTypes.object,
      PropTypes.array
    ]).isRequired
  };

  render() {
    return (
      <div className="ui top attached tabular menu">
        {this.props.children}
      </div>
    );
  }
}
export default TabsMenu;
