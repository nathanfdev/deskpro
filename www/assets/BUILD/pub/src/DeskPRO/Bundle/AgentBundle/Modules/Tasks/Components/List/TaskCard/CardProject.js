import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { QuickFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';
import { CardWidget } from './CardWidget';

export class CardProject extends CardWidget {

  static propTypes = {
    value:             PropTypes.number,
    projects:          PropTypes.object.isRequired,
    openBySingleClick: PropTypes.bool,
    onSetEditing:      PropTypes.func,
    onChange:          PropTypes.func.isRequired,
    withoutIcon:       PropTypes.bool,
    className:         PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen:   props.isOpen,
      projects: props.projects,
      value:    props.value
    };
  }

  shouldComponentUpdate(props, state) {
    return this.state.isOpen !== state.isOpen
      || this.state.value !== state.value
      || !Immutable.is(this.state.projects, state.projects);
  }

  componentWillReceiveProps(props) {
    const state = {
      value:    props.value,
      projects: props.projects
    };
    if (undefined !== props.isOpen) {
      state.isOpen = props.isOpen;
    }
    this.setState(state);
  }

  filterProjects = (str) => {
    const search = (str || '').toLowerCase();
    this.setState({
      projects: this.props.projects.filter((item) => (item.get('title', '')).toLowerCase().indexOf(search) !== -1)
    });
  };

  onChange = (value) => {
    this.setState({ value: this.state.value === value ? null : value });
  };

  onClose = () => {
    if (!this.state.isOpen) return;
    this.setState({ isOpen: false });
    if (this.props.onSetEditing) {
      this.props.onSetEditing(false);
    }
    if (this.props.onChange) {
      this.props.onChange(this.state.value);
    }
  };

  render() {
    const { withoutIcon } = this.props;
    const { projects, value } = this.state;
    const project = value ? projects.get(value) : null;
    const prop = { [this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen };
    const title = project ? project.get('title') : 'N/A';
    const iconStyle = {
      display:     'inline-block',
      position:    'relative',
      paddingLeft: 20,
      overflow:    'hidden',
      width:       '100%'
    };

    return (
      <div className={this.props.className}>
        {withoutIcon
          ? <span title={title} ref="button" {...prop}>{title}</span>
          : <div className="dpwd--card-line-item" ref="button" {...prop} style={iconStyle}>
              <i className="fa fa-book" style={{ position: 'absolute', left: 2, top: 2 }} />
              <span title={title}>{title}</span>
            </div>
        }

        <Positioned isOpen={this.state.isOpen} positionTarget={this} positionAt="right+5 top-23" collision="fit"
          zIndex={1002}
        >

          <ClickOut onClickOut={this.onClose} additionalNodes={[this.refs.button, '.fa-check']}>

            <div className="dpw-navigation-dropdown-panel">
              <div className="dpw-navigation-dropdown-panel-content">
                <div className="dpw-navigation-dropdown-panel-content-line">
                  <div className="dpw-navigation-dropdown-panel-content-full">
                    <div className="dpw-departments-long-list">
                      <QuickFilter onChange={this.filterProjects} />
                      <div className="dpw--popup-item-collection">
                        <ul>
                          {projects.entrySeq().map(([id, item]) =>
                            <li key={id}>
                              <div className="dpw--popup-item-box" onClick={() => this.onChange(id)}>
                                <span className="dpw--checkbox-boxy">
                                  {project === item ? <i className="fa fa-check"></i> : null}
                                </span>
                                <span className="dpw-popup-item-collection-name">
                                  {item.get('title')}
                                </span>
                              </div>
                            </li>
                          )}
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </ClickOut>
        </Positioned>
      </div>
    );
  }
}
