import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { MenuWrapper, Menu } from 'DeskPRO/Component/Semantic/Menu';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import MenuItem from 'DeskPRO/Component/Semantic/Menu/MenuItem';
import * as actions from '../../Actions/templatesActions';
import { replaceRoute } from '../../../../Services/history';


@connect(state => ({
  portalEditor: state.Portal.templates
}))
export class TemplatesMenuContainer extends React.Component {
  static propTypes = {
    brandId:      PropTypes.string,
    dispatch:     PropTypes.func,
    closeMenu:    PropTypes.func,
    portalEditor: PropTypes.object
  };

  onChangeTemplate = (template) => {
    this.props.dispatch(actions.setCurrentTemplate(template));
    this.props.dispatch(actions.loadTemplate(template.get('value'))).then(
      (data) => {
        replaceRoute(`/portal/${this.props.brandId}/templates_editor/${template.get('value').replace('/', '|')}`);
        this.props.dispatch(actions.setTemplate(data));
      }
    );
    setTimeout(() => this.props.closeMenu(), 100);
  };

  render() {
    const templates = this.props.portalEditor.getIn(['info'], null);
    return (
      <TemplatesMenu
        templates={templates}
        selectTemplate={this.onChangeTemplate}
      />
    );
  }
}
class TemplatesMenu extends React.Component {
  static propTypes = {
    templates:      PropTypes.object,
    selectTemplate: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLeft: null,
      filter:       '',
    };
  }

  componentWillMount() {
    if (this.props.templates) {
      this.setState({
        selectedLeft: this.props.templates.first()
      });
    }
    const button = window.document.getElementsByClassName('emails-block-button')[0];
    this.coverWidth = button.offsetWidth;
  }

  setActive = (item) => {
    this.setState({
      selectedLeft: item
    });
  };

  getRightPanel = () => {
    const { selectedLeft, filter } = this.state;
    if (!selectedLeft) {
      return null;
    }
    const templates = selectedLeft.get('templates');
    return (
      <MenuWrapper className={classNames('right-panel')}>
        <Menu title={selectedLeft.get('title')}>
          {
            templates
              .filter(template => !filter || template.get('name').toLowerCase().indexOf(filter.toLowerCase()) !== -1)
              .map(template =>
                <MenuItem
                  key={template.get('value')}
                  icon="code"
                  className="template"
                  onClick={() => this.props.selectTemplate(template)}
                >
                  {template.get('name')}
                </MenuItem>
            ).toArray()
          }
        </Menu>
      </MenuWrapper>
    );
  };

  getGroups = () => {
    const { filter } = this.state;
    if (!this.props.templates) {
      return null;
    }
    return this.props.templates.toSeq()
      .filter(group =>
        !filter || group.get('templates')
          .find(template => template.get('name').toLowerCase().indexOf(filter.toLowerCase()) !== -1)
      )
      .map((template, group) => (
        <MenuItem
          key={`template${group}`}
          label={group}
          icon="folder open"
          className={classNames({ active: this.isActive(template) })}
          onClick={() => this.setActive(template)}
        />
      )).toArray();
  };

  updateFilter = (filter) => {
    this.setState({
      filter
    });
    if (this.state.selectedLeft && this.state.selectedLeft.get('templates').filter(template => !filter || template.get('name').toLowerCase().indexOf(filter.toLowerCase()) !== -1).size === 0) {
      const selectedLeft = this.props.templates.toSeq()
        .filter(group =>
          !filter || group.get('templates')
            .find(template => template.get('name').toLowerCase().indexOf(filter.toLowerCase()) !== -1)
        ).first();
      this.setState({
        selectedLeft
      });
    }
  };

  isActive = item => item === this.state.selectedLeft;

  render() {
    return (
      <div className="email-and-block two-panels-menu">
        <div className="menu-button-cover" style={{ width: this.coverWidth + 20 }} />
        <MenuWrapper>
          <SearchBox
            onUserInput={this.updateFilter}
          />
          <Menu title="Templates">
            {this.getGroups()}
          </Menu>
        </MenuWrapper>
        {this.getRightPanel()}
      </div>
    );
  }
}
