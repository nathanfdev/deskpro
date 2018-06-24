import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars

export class BtnPin extends React.PureComponent {
  static propTypes = {
    toggle: PropTypes.func.isRequired
  };

  onClick = (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    this.props.toggle();
  };

  render()  {
    return (
      <div className={'layout-sidebar__toggle pin-btn'} onClick={this.onClick} >
        <i className={'fa fa-lock'} />
        <i className={'fa fa-chevron-right'} />
      </div>
    );
  }
}
