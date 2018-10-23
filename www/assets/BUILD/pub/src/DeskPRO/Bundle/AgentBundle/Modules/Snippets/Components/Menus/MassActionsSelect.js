import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import { faDownload, faPencilAlt, faTag, faUsers } from '@fortawesome/free-solid-svg-icons';
import { faEnvelope, faEye } from '@fortawesome/free-regular-svg-icons';
import { Icon, CustomSelect, List, ListElement } from '@deskpro/react-components';

export class MassActionsSelect extends React.PureComponent {
  static propTypes = {
    onChange: PropTypes.func,
  };

  static getActions() {
    return [
      {
        value: 'labels',
        icon:  faTag,
        text:  <FormattedMessage id="agent.general.labels" />,
      },
      {
        value: 'visibility',
        icon:  faEye,
        text:  <FormattedMessage id="agent.snippets.visibility" />,
      },
      {
        value: 'ownership',
        icon:  faUsers,
        text:  <FormattedMessage id="agent.snippets.ownership" />,
      },
      {
        value: 'type',
        icon:  faEnvelope,
        text:  <FormattedMessage id="agent.general.type" />,
      },
      {
        value: 'export',
        icon:  faDownload,
        text:  <FormattedMessage id="agent.general.export" />,
      },
      {
        value: 'draft',
        icon:  faPencilAlt,
        text:  <FormattedMessage id="agent.snippets.draft_status" />,
      },
    ];
  }

  onChange = (langId) => {
    this.props.onChange(langId);
    this.select.toggleOpened();
  };

  inputRenderer = () => <span key="label"><FormattedMessage id="agent.general.mass_actions" /></span>;

  render() {
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
        className="mass-actions"
        ref={(c) => { this.select = c; }}
      >
        <List
          className="dp-selectable-list"
        >
          {MassActionsSelect.getActions().map(action =>
            <ListElement
              key={action.value}
              onClick={() => this.onChange(action.value)}
            >
              <Icon name={action.icon} /> {action.text}
            </ListElement>
          )}
        </List>
      </CustomSelect>
    );
  }
}
