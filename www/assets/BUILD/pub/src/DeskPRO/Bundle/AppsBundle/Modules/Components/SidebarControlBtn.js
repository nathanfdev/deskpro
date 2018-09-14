import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars

export class SidebarControlBtn extends React.PureComponent {

  static propTypes = {
    onActivate:   PropTypes.func.isRequired,
    sidebarState: PropTypes.oneOf(['expanded', 'collapsed', 'pinned']).isRequired
  };

  onClick = (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    this.props.onActivate();
  };

  render()  {
    return (
      <div className={'pin-btn dp-ButtonTabs dp-AppTabs--control'} onClick={this.onClick} >

        {this.props.sidebarState === 'expanded' && <i className="dp-IconLock" /> }
        {this.props.sidebarState === 'collapsed' && <i className="dp-IconArrow iconArrow--left" /> }
        {this.props.sidebarState === 'pinned' && <i className="dp-IconArrow iconArrow--right" /> }

      </div>
    );
  }
}
