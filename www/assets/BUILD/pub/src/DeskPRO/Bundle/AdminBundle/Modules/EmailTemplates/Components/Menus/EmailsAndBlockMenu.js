import React, { PropTypes } from 'react';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import Accordion from 'DeskPRO/Component/Semantic/Accordion/Accordion';
import classNames from 'classnames';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';

export class EmailsAndBlockMenuContainer extends React.Component {

  render() {
    return <EmailsAndBlockMenu />;
  }
}

export class EmailsAndBlockMenu extends React.Component {
  static propTypes = {
    emails:       PropTypes.object,
    emailBlocks:  PropTypes.object,
    onChangeMenu: PropTypes.func
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
    return this.props.emails.map((email, key) =>
      <MenuItem
        key={`email${key}`}
        label={email.get('label')}
        icon={email.get('icon')}
        subContent={email.get('subContent').get('sections')}
        classes={classNames({ active: this.isActive(email) })}
        onClick={() => this.setActive(email)}
      />
    );
  };

  getEmailBlocks = () => {
    if (!this.props.emailBlocks) {
      return null;
    }
    const self = this;
    return this.props.emailBlocks.map((emailBlock, key) =>
      <MenuItem
        key={`emailBlock${key}`}
        label={emailBlock.get('label')}
        icon={emailBlock.get('icon')}
        classes={classNames({ active: self.isActive(emailBlock) })}
        onClick={() => self.setActive(emailBlock)}
      />
    );
  };

  getRightPanel = () => {
    if (!this.state.selectedLeft || !this.state.selectedLeft.get('subContent').get('sections')) {
      return null;
    }
    const subContentSections = this.state.selectedLeft.get('subContent').get('sections');
    const primary = subContentSections.get('primary').map((entry, key) =>
      <ListElement
        key={`primary${key}`}
        label={entry.get('label')}
        icon={entry.get('icon')}
      />
    );
    return (
      <MenuWrapper classes={classNames('right-panel')}>
        <h4>Primary</h4>
        <List>{primary}</List>
        <h4>Custom</h4>
        <Accordion />
        <h4>Additional templates</h4>
        <Accordion />
      </MenuWrapper>
    );
  };

  isActive = (item) => item === this.state.selectedLeft;

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
