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
      props:  {},
      width:  600
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpLeftDrawer', (e) => {
      let module;
      let props;
      let width = e.detail.width;
      switch (e.detail.module) {
        case 'SnippetsMenu': {
          const type = e.detail.type ? e.detail.type : 'ticket';
          const departmentId = e.detail.department ? e.detail.department : 0;
          const splitter = document.getElementById('dp_list_resizer');
          if (splitter.className.match(/\bng-hide\b/)) {
            width = 725;
          } else {
            const rect = splitter.getBoundingClientRect();
            width = Math.max(rect.left - 46, 725);
          }
          module = SnippetsMenuContainer;
          props = {
            closeMenu:     this.closeDrawer,
            type,
            width,
            langId:        parseInt(e.detail.langId, 10),
            department:    departmentId,
            insertSnippet: e.detail.insertSnippet,
          };
          if (e.detail.onClose) {
            this.onClose = e.detail.onClose;
          }
          break;
        }
        default:
          module = false;
      }
      if (module) {
        if (this.state.active) {
          this.closeDrawer();
        } else {
          this.setState({
            module,
            props,
            width
          });
          this.openDrawer();
        }
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
    window.document.addEventListener('dpChangeSection', () => {
      this.closeDrawer();
    });
  };

  openDrawer = () => {
    this.setState({
      active: true
    });
  };

  closeDrawer = () => {
    if (this.onClose) {
      this.onClose();
    }
    if (this.module && this.module.getWrappedInstance().onClose) {
      this.module.getWrappedInstance().onClose();
    }
    this.setState({
      active: false
    });
  };

  render() {
    const { active, width } = this.state;
    const style = { width: active ? width : 0 };
    const props = this.state.props;
    const Module = this.state.module;
    if (Module) {
      return (<LeftDrawer active={active} style={style}>
        <Module open={active} {...props} ref={(c) => { this.module = c; }} />
      </LeftDrawer>);
    }
    return <LeftDrawer active={active} style={style} />;
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
