import React, { PropTypes } from 'react';
import classNames from 'classnames';

class TabsMenu extends React.Component {

  static propTypes = {
    children: PropTypes.object.isRequired
  };

  render() {
    return (
      <div className={classNames('ui', 'top', 'attached', 'tabular', 'menu')}>
        {this.props.children}
      </div>
    );
  }
}
export default TabsMenu;
