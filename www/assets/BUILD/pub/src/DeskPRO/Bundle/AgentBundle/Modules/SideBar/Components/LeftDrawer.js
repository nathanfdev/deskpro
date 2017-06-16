import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { SnippetsMenuContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsMenu';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';

export class LeftDrawerContainer extends SeparateComponent {
  static getType() {
    return 'LeftDrawerContainer';
  }

  constructor(props) {
    super(props);
    this.state = {
      active: false,
      module: null,
      width:  600
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpLeftDrawer', (e) => {
      let module;
      switch (e.detail.module) {
        case 'SnippetsMenu':
          module = <SnippetsMenuContainer closeMenu={this.closeDrawer} type="ticket" />;
          break;
        default:
          module = false;
      }
      if (module) {
        this.setState({
          module,
          width: e.detail.width
        });
        this.openDrawer();
      } else {
        this.setState({
          module: null,
          active: false
        });
      }
    });
    window.document.addEventListener('dpLeftDrawerClose', () => {
      this.closeDrawer();
    });
  };

  openDrawer = () => {
    this.setState({
      active: true
    });
  };

  closeDrawer = () => {
    this.setState({
      active: false
    });
  };

  render() {
    const { active, width } = this.state;
    const style = { width: active ? width : 0 };
    return <LeftDrawer active={active} style={style}>{this.state.module}</LeftDrawer>;
  }
}

export class LeftDrawer extends React.Component {
  static propTypes = {
    active:   PropTypes.bool,
    children: PropTypes.node,
    style:    PropTypes.object,
  };

  static defaultProps = {
    active: false
  };

  render() {
    const { active, children, style } = this.props;
    return (
      <div className={classNames('left-drawer', { active })} style={style}>
        {children}
      </div>
    );
  }
}
export default LeftDrawerContainer;
