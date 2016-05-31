import React, { Component, PropTypes } from 'react';
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
    department:     PropTypes.object.isRequired,
    selected:       PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  render() {
    const { chat, selected, toggleSelected, author, agent, department } = this.props;

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
          <CardLineLeft>
            <CardUser user={author} />
            <CardDisc />
            <CardUser user={agent} />
            {department && <CardDisc />}
            {department && <CardLineItem>{department.get('title')}</CardLineItem>}
          </CardLineLeft>
        </CardLine>
      </Card>
    );
  }
}
