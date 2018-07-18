import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { SnippetsMenuContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Snippets/Components/SnippetsMenu';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import SearchContainer from 'DeskPRO/Bundle/AgentBundle/Modules/Search/Components/SearchContainer';

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
      width:  600,
      zIndex: null,
      style:  {},
    };
    this.ticking  = false;
    this.splitter = document.getElementById('dp_list_resizer');
  }

  componentWillMount = () => {
    window.document.addEventListener('dpLeftDrawer', (e) => {
      let module;
      let props;
      switch (e.detail.module) {
        case 'SnippetsMenu': {
          const type = e.detail.type ? e.detail.type : 'ticket';
          const departmentId = e.detail.department ? e.detail.department : 0;
          this.resize();
          module = SnippetsMenuContainer;
          props = {
            closeMenu:     this.closeDrawer,
            type,
            langId:        parseInt(e.detail.langId, 10),
            department:    departmentId,
            insertSnippet: e.detail.insertSnippet,
          };
          if (e.detail.onClose) {
            this.onClose = e.detail.onClose;
          }
          break;
        }
        case 'Search': {
          this.resize();
          module = SearchContainer;
          props = {
            closeMenu: this.closeDrawer,
          };
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
            style: e.detail.style ? e.detail.style : {},
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
    window.document.addEventListener('dpLeftDrawerClose', this.closeDrawer);
    window.document.addEventListener('dpChangeSection', this.closeDrawer);
  };

  componentWillUnmount() {
    window.document.removeEventListener('dpLeftDrawerClose', this.closeDrawer);
    window.document.removeEventListener('dpChangeSection', this.closeDrawer);
  }

  componentDidUpdate() {
    if (this.state.active) {
      const event = new CustomEvent('dpLeftDrawerOpened');
      window.document.dispatchEvent(event);
    }
  }

  openDrawer = () => {
    this.setState({
      active: true
    });
    DeskPRO_Window.layout.addEvent('resized', this.resize); // eslint-disable-line no-undef
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

  resize = () => {
    let width;
    if (!window.DeskPRO_Window.paneVis.tabs || !window.DeskPRO_Window.paneVis.list) {
    // if (this.splitter.className.match(/\bng-hide\b/)) {
      width = 725;
    } else {
      const rect = this.splitter.getBoundingClientRect();
      width = Math.max(rect.left - 46, 725);
    }
    this.setState({ width });
  };

  render() {
    const { active, width } = this.state;
    const style = { width: active ? width : 0, ...this.state.style };
    const props = this.state.props;
    const Module = this.state.module;
    if (Module) {
      return (<LeftDrawer active={active} style={style}>
        <Module
          open={active}
          width={width}
          {...props}
          ref={(c) => { this.module = c; }}
        />
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
