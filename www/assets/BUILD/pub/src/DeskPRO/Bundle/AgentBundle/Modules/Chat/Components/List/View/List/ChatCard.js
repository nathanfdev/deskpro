import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineFull, CardContentText, CardLineItem, CardCheckbox, CardDisc, CardUser }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

@injectIntl
export class ChatCard extends Component {
  static propTypes = {
    intl: intlShape.isRequired,
    chat: PropTypes.object.isRequired,
    author: PropTypes.object.isRequired,
    agent: PropTypes.object.isRequired,
    department: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  render() {
    const { chat, selected, toggleSelected, author, agent, department } = this.props;

    return (
      <Card type="chat">
        <div className="card-status-bar status-bar-left level-8"></div>
        <div className="card-status-bar status-bar-right level-8"></div>

        <CardCheckbox selected={selected} onClick={toggleSelected(chat.get('id'))}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>#{chat.get('id')}</CardLineItem>
          </CardLineLeft>

          <CardLineRight>
            <CardLineItem>
              <FormattedRelative value={chat.get('date_created')}/>
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
            <CardUser user={author}/>
            <CardDisc/>
            <CardUser user={agent}/>
            <CardDisc/>
            <CardLineItem>{department && department.get('title')}</CardLineItem>
            <CardDisc/>
            <span className="text"></span> <i className="fa fa-comment"></i>
          </CardLineLeft>

          <CardLineRight>
            <i className="fa fa-book"></i> <span className="chat-type"></span>
            <span className="disc"></span>
            <i className="fa fa-book"></i> <span
            className="chat-custom-category"></span>
            <span className="disc"></span>
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }
}