import React, { PropTypes } from 'react';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { Accordion, AccordionPanel } from 'DeskPRO/Component/Semantic/Accordion';
import classNames from 'classnames';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';

export class EmailsAndBlockMenuContainer extends React.Component {

  render() {
    return <EmailsAndBlockMenu />;
  }
}

export class EmailsAndBlockMenu extends React.Component {
  static propTypes = {
    emails:         PropTypes.object,
    emailBlocks:    PropTypes.object,
    onChangeMenu:   PropTypes.func,
    selectTemplate: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLeft: null
    };
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
      <MenuItem
        key={`emailBlock${key}`}
        label={template.get('title')}
        icon="code"
        className="template"
        onClick={() => this.props.selectTemplate(template)}
        onMouseOver={e => this.templateToolTip(e, template)}
        onMouseOut={this.cancelToolTip}
      />
    );
  };

  getRightPanel = () => {
    if (!this.state.selectedLeft || !this.state.selectedLeft.get('subGroups')) {
      return null;
    }
    const subGroups = this.state.selectedLeft.get('subGroups');
    const primary = subGroups.get('primary').get('templates').valueSeq().map((template, key) =>
      <MenuItem
        key={`primary${key}`}
        label={template.get('title')}
        icon="code"
        className="template"
        onClick={() => this.props.selectTemplate(template)}
        onMouseOver={e => this.templateToolTip(e, template)}
        onMouseOut={this.cancelToolTip}
      />
    );
    const additionalTemplates = subGroups.valueSeq().map((subGroup) => {
      if (subGroup.get('subGroupId') === 'primary') {
        return null;
      }
      let content = subGroup.get('templates').valueSeq().map((template, key) =>
        <MenuItem
          key={`${subGroup.get('subGroupId')}${key}`}
          label={template.get('title')}
          icon="code"
          className="template"
          onClick={() => this.props.selectTemplate(template)}
          onMouseOver={e => this.templateToolTip(e, template)}
          onMouseOut={this.cancelToolTip}
        />);
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

  templateToolTip = (e, template) => {
    this.toolTipTimeOutId = setTimeout(() => {
      console.log(template.get('desc'));
      console.log(e);
    }, 500);
  };

  cancelToolTip = () => {
    window.clearTimeout(this.toolTipTimeOutId);
    this.toolTipTimeOutId = undefined;
  };

  isActive = item => item === this.state.selectedLeft;

  render() {
    return (
      <div className="email-and-block two-panels-menu">
        <MenuWrapper>
          <SearchBox />
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
