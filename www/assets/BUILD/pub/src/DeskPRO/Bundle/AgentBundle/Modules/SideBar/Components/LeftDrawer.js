import React, { PropTypes } from 'react';
import classNames from 'classnames';
import SnippetsMenuContainer from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsMenu';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';

export class LeftDrawerContainer extends SeparateComponent {
  static getType() {
    return 'LeftDrawerContainer';
  }

  constructor(props) {
    super(props);
    this.state = {
      active: false,
      module: null
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpLeftDrawer', (e) => {
      let Module;
      switch (e.detail.module) {
        case 'SnippetsMenu':
          Module = SnippetsMenuContainer;
          break;
        default:
          Module = false;
      }
      if (Module) {
        this.setState({
          module: <Module />
        });
        this.openDrawer();
      } else {
        this.setState({
          module: null,
          active: false
        });
      }
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
    const { active } = this.state;
    return <LeftDrawer active={active}>{this.state.module}</LeftDrawer>;
  }
}

export class LeftDrawer extends React.Component {
  static propTypes = {
    active:   PropTypes.bool,
    children: PropTypes.node,
  };

  static defaultProps = {
    active: false
  };

  render() {
    const { active, children } = this.props;
    return (
      <div className={classNames('left-drawer', { active })}>
        {children}
      </div>
    );
  }
}
export default LeftDrawerContainer;
