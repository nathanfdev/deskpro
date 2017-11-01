import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import {
  Card, CardLine, CardLineLeft, CardLineRight, CardLineFull,
  CardContentText, CardLineItem, CardCheckbox, CardDisc, CardUser
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

@injectIntl
export class ChatCard extends Component {
  static propTypes = {
    intl:           intlShape.isRequired,
    chat:           PropTypes.object.isRequired,
    author:         PropTypes.object.isRequired,
    agent:          PropTypes.object.isRequired,
    department:     PropTypes.object,
    selected:       PropTypes.bool.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    fields:         PropTypes.object.isRequired
  };

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');
    const { agent, author, department } = this.props;

    switch (fieldId) {

      case 'person':
        return <CardLineLeft key={fieldId}><CardUser user={author} /></CardLineLeft>;

      case 'agent':
        return <CardLineLeft key={fieldId}><CardUser user={agent} /></CardLineLeft>;

      case 'department':
        return (
          <CardLineLeft key={fieldId}>
            <CardLineItem>
              {department && department.get('title')}
            </CardLineItem>
          </CardLineLeft>
        );

      default:
        return null;
    }
  }

  render() {
    const { chat, selected, toggleSelected, fields } = this.props;

    return (
      <Card type="chat">
        <div className="card-status-bar status-bar-left level-8"></div>
        <div className="card-status-bar status-bar-right level-8"></div>

        <CardCheckbox selected={selected} onClick={toggleSelected(chat.get('id'))} />

        <CardLine>
          <CardLineLeft>
            <CardLineItem>#{chat.get('id')}</CardLineItem>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem>
              <FormattedRelative value={chat.get('date_created')} />
            </CardLineItem>
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineFull>
            <CardContentText>
              <p>{chat.get('subject')}</p>
            </CardContentText>
          </CardLineFull>
        </CardLine>

        <CardLine>
          {fields.map(field => this.renderField(field))}
        </CardLine>
      </Card>
    );
  }
}
