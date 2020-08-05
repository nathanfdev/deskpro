import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { fromJS } from 'immutable';
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
    brandSlug:    PropTypes.string,
    dispatch:     PropTypes.func,
    closeMenu:    PropTypes.func,
    portalEditor: PropTypes.object
  };

  onChangeTemplate = (template) => {
    this.props.dispatch(actions.setCurrentTemplate(template));
    this.props.dispatch(actions.loadTemplate(template.get('value'), this.props.brandSlug)).then(
      (data) => {
        this.props.dispatch(actions.setCurrentTemplate(fromJS(data)));
        replaceRoute(`/portal/${this.props.brandId}/templates_editor/${template.get('value').replace('/', '|')}`);
        this.props.dispatch(actions.setTemplate(data));
      }
    );
    setTimeout(() => this.props.closeMenu(), 100);
  };

  setSelectedLeft = (item) => {
    this.props.dispatch(actions.setTemplateSelectedLeft(item));
  }

  render() {
    const templates = this.props.portalEditor.getIn(['info'], null);
    return (
      <TemplatesMenu
        templates={templates}
        selectedLeft={this.props.portalEditor.get('selectedLeft', null)}
        setSelectedLeft={this.setSelectedLeft}
        selectTemplate={this.onChangeTemplate}
      />
    );
  }
}
class TemplatesMenu extends React.Component {
  static propTypes = {
    templates:       PropTypes.object,
    selectedLeft:    PropTypes.object,
    selectTemplate:  PropTypes.func,
    setSelectedLeft: PropTypes.func,
  };

  static defaultProps = {
    selectLeft: null
  };

  static getMenuLabel = (template) => {
    if (template.get('custom', false)) {
      return <span>{template.get('name')} <span title="modified">(*)</span></span>;
    }
    return template.get('name');
  };

  constructor(props) {
    super(props);
    this.state = {
      filter: '',
    };
  }

  componentWillMount() {
    if (this.props.templates && !this.props.selectedLeft) {
      this.props.setSelectedLeft(this.props.templates.first());
    }
    const button = window.document.getElementsByClassName('emails-block-button')[0];
    this.coverWidth = button.offsetWidth;
  }

  setActive = (item) => {
    this.props.setSelectedLeft(item);
  };

  getRightPanel = () => {
    const { filter } = this.state;
    const { selectedLeft } = this.props;
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
              .sortBy(template => template.get('name'))
              .map(template =>
                <MenuItem
                  key={template.get('value')}
                  icon="code"
                  className="template"
                  onClick={() => this.props.selectTemplate(template)}
                >
                  {TemplatesMenu.getMenuLabel(template)}
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
      .sortBy(group => group.get('title'))
      .map((template, group) => (
        <MenuItem
          key={`template${group}`}
          label={group}
          icon="folder open"
          className={classNames({ active: this.isActive(template) })}
          onClick={() => this.setActive(template)}
        />
      ))
      .toArray();
  };

  updateFilter = (filter) => {
    this.setState({
      filter
    });
    if (this.props.selectedLeft
      && this.props.selectedLeft.get('templates')
        .filter(
          template => !filter || template.get('name').toLowerCase().indexOf(filter.toLowerCase()) !== -1
        ).size === 0) {
      const selectedLeft = this.props.templates.toSeq()
        .filter(group =>
          !filter || group.get('templates')
            .find(template => template.get('name').toLowerCase().indexOf(filter.toLowerCase()) !== -1)
        ).first();
      this.props.setSelectedLeft(selectedLeft);
    }
  };

  isActive = item => item === this.props.selectedLeft;

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
