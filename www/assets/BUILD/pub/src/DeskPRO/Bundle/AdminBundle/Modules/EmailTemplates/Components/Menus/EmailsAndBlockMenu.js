import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { Accordion, AccordionPanel } from 'DeskPRO/Component/Semantic/Accordion';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import EmailTemplateItem from './EmailTemplateItem';
import * as actions from '../../Actions/templatesActions';

@connect(state => ({
  emailTemplates: state.EmailTemplates.templates
}))
export class EmailsAndBlockMenuContainer extends React.Component {
  static propTypes = {
    dispatch:       PropTypes.func,
    emailTemplates: PropTypes.object.isRequired,
    closeMenu:      PropTypes.func
  };

  onChangeTemplate = (template) => {
    this.props.dispatch(actions.setCurrentTemplate(template));
    this.props.dispatch(actions.loadTemplate(template.get('newTemplate')));
    if (template.get('viewModel')) {
      this.props.dispatch(actions.loadVariables(template.get('viewModel')));
    } else {
      this.props.dispatch(actions.removeVariables());
    }
    this.props.closeMenu();
  };

  render() {
    const info = this.props.emailTemplates;
    let templates = null;
    const group = info.get('currentTemplateGroup');
    if (info && info.get('info').get('list')) {
      templates = info.get('info').get('list').get(group).get('groups');
    }
    return (<EmailsAndBlockMenu
      emails={templates}
      selectTemplate={this.onChangeTemplate}
    />);
  }
}

export class EmailsAndBlockMenu extends React.Component {
  static propTypes = {
    emails:         PropTypes.object,
    emailBlocks:    PropTypes.object,
    onChangeMenu:   PropTypes.func,
    selectTemplate: PropTypes.func
  };
  static defaultProps = {
    onChangeMenu() {},
    selectTemplate() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLeft: null,
      filter:       ''
    };
  }

  componentWillMount() {
    if (this.props.emails) {
      this.setState({
        selectedLeft: this.props.emails.first()
      });
    }
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      selectedLeft: nextProps.emails.first()
    });
  }

  setActive = (item) => {
    this.props.onChangeMenu(item);
    this.setState({
      selectedLeft: item
    });
  };

  getEmails = () => {
    if (!this.props.emails) {
      return null;
    }
    return this.props.emails.valueSeq().map((email, key) =>
      <MenuItem
        key={`email${key}`}
        label={email.get('title')}
        icon="folder open"
        subContent={email.get('subGroups')}
        className={classNames({ active: this.isActive(email) })}
        onClick={() => this.setActive(email)}
      />
    );
  };

  getEmailBlocks = () => {
    if (!this.props.emailBlocks) {
      return null;
    }
    return this.props.emailBlocks.valueSeq().map((template, key) =>
      <EmailTemplateItem
        key={`emailBlock${key}`}
        label={template.get('title')}
        icon="code"
        className="template"
        onClick={() => this.props.selectTemplate(template)}
        desc={template.get('desc')}
      />
    );
  };

  getRightPanel = () => {
    if (!this.state.selectedLeft || !this.state.selectedLeft.get('subGroups')) {
      return null;
    }
    const subGroups = this.state.selectedLeft.get('subGroups');
    const primary = subGroups.get('primary').get('templates').valueSeq().map((template, key) => {
      if (
        this.state.filter
        && template.get('title').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
        && template.get('desc').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
      ) {
        return null;
      }
      return (<EmailTemplateItem
        key={`primary${key}`}
        label={template.get('title')}
        icon="code"
        className="template"
        onClick={() => this.props.selectTemplate(template)}
        desc={template.get('desc')}
      />);
    });
    const additionalTemplates = subGroups.valueSeq().map((subGroup) => {
      if (subGroup.get('subGroupId') === 'primary') {
        return null;
      }
      let content = subGroup.get('templates').valueSeq().map((template, key) => {
        if (
          this.state.filter
          && template.get('title').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
          && template.get('desc').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
        ) {
          return null;
        }
        return (<EmailTemplateItem
          key={`${subGroup.get('subGroupId')}${key}`}
          label={template.get('title')}
          icon="code"
          className="template"
          onClick={() => this.props.selectTemplate(template)}
          desc={template.get('desc')}
        />);
      });
      content = <Menu>{content}</Menu>;
      return (<AccordionPanel
        key={`addEl${subGroup.get('subGroupId')}`}
        panel={{
          title:       subGroup.get('title'),
          subElements: subGroup.get('templates').size,
          icon:        'folder open',
          content
        }}
      />);
    });
    return (
      <MenuWrapper className={classNames('right-panel')}>
        <Menu title={subGroups.get('primary').get('title')}>
          {primary}
        </Menu>
        <h4>Additional templates</h4>
        <Accordion>{additionalTemplates}</Accordion>
      </MenuWrapper>
    );
  };

  updateFilter = (value) => {
    this.setState({
      filter: value
    });
  };

  isActive = item => item === this.state.selectedLeft;

  render() {
    return (
      <div className="email-and-block two-panels-menu">
        <MenuWrapper>
          <SearchBox
            onUserInput={this.updateFilter}
          />
          <Menu title="Emails">
            {this.getEmails()}
          </Menu>
          <Menu title="Email blocks">
            {this.getEmailBlocks()}
          </Menu>
        </MenuWrapper>
        {this.getRightPanel()}
      </div>
    );
  }
}
