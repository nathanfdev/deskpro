import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { CardWidget, SearchResults } from './index';
import { quickSearchAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/search';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import Select from 'react-select-plus';

export class LinkedItem extends CardWidget {

  static propTypes = {
    value: PropTypes.object,

    openBySingleClick: PropTypes.bool,
    onSetEditing: PropTypes.func,
    onChange: PropTypes.func,

    tickets: PropTypes.object,
    chats: PropTypes.object,
    articles: PropTypes.object,

    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    const empty = Immutable.fromJS({});
    this.state = {
      isOpen: props.isOpen,
      value: Immutable.fromJS({
        linked_tickets: props.value.get('linked_tickets') || empty,
        linked_chats: props.value.get('linked_chats') || empty,
        linked_articles: props.value.get('linked_articles') || empty
      })
    };
  }

  componentWillReceiveProps(props) {
    const empty = Immutable.fromJS({});
    this.setState({
      value: Immutable.fromJS({
        linked_tickets: props.value.get('linked_tickets') || empty,
        linked_chats: props.value.get('linked_chats') || empty,
        linked_articles: props.value.get('linked_articles') || empty
      })
    });

    if (undefined !== props.isOpen) {
      this.setState({isOpen: props.isOpen});
    }
  }

  shouldComponentUpdate(props, state) {
    return this.state.isOpen !== state.isOpen || this.state.value !== state.value
      || !Immutable.is(this.state.value, state.value);
  }

  getOptions = (input) => {
    const type = ['ticket', 'article', 'chat_conversation'];
    return new Promise((resolve, reject) => {
      this.props.dispatch(quickSearchAction({type: type, query: input})).then((res) => {
        let results = [];
        if (!res.data || !res.data.grouped_results) resolve({options: results});

        for (let group of res.data.grouped_results) {
          let option = {label: '', options: []};
          results.push(option);

          if (group.type === 'ticket') {
            option.label = 'Tickets';
          } else if (group.type === 'chat') {
            option.label = 'Chats';
          } else if (group.type === 'article') {
            option.label = 'Articles';
          }

          for (let result of group.results) {
            if (group.type === 'ticket') {
              option.options.push({
                label: result.subject,
                value: result.id
              });
            } else  if (group.type === 'chat') {
              option.options.push({
                label: result.subject,
                value: result.id
              });
            } else  if (group.type === 'article') {
              option.options.push({
                label: result.title,
                value: result.id
              });
            }
          }
        }
        resolve({options: results});
      });
    });
  };

  render() {
    const prop = {[this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen};
    const tickets = this.state.value.get('linked_tickets');
    const chats = this.state.value.get('linked_chats');
    const articles = this.state.value.get('linked_articles');
    const count = tickets.size + chats.size + articles.size;
    let title = 'N/A';

    if (1 === count) {
      if (tickets.size) {
        title = 'Linked ticket: ' + tickets.first().get('subject');
      } else if (chats.size) {
        title = 'Linked chat: ' + chats.first().get('subject');
      } else if (articles.size) {
        title = 'Linked article: ' + articles.first().get('title');
      }
    } else if (count > 1) {
      title = count + ' linked items';
    }

    return (
      <div style={{display: 'inline-block', maxWidth: '30%'}}>
        <div className="dpwd--card-line-item" {...prop}
             style={{display: 'inline-block', position: 'relative', paddingLeft: 20, overflow: 'hidden', width: '100%'}}>
          <i className="fa fa-link" style={{position: 'absolute', left: 2, top: 2}} />
          <span title={title}>{title}</span>
        </div>

        <Positioned isOpen={this.state.isOpen}
                    positionTarget={this}
                    positionAt="right+5 top-23"
                    collision="fit"
                    zIndex={1002}>

          <ClickOut onClickOut={this.onClose}
                    onClick={this.test}
                    additionalNodes={[this.refs.button, 'popup']}>
            <div className="dpw-navigation-dropdown-panel">
              <div className="dpw-navigation-dropdown-panel-content">
                <div className="dpw-navigation-dropdown-panel-content-line">
                  <div className="dpw-navigation-dropdown-panel-content-full">

                    <div className="dpw-label-pile">
                      <ul className="dpw-label-list">
                        {tickets.size && tickets.map((ticket) =>
                          <li>
                            <span className="dpw-item-label">
                              <i className="fa fa-times"></i>
                              {ticket.get('subject')}
                            </span>
                          </li>
                        ) || null}

                        {chats.size && chats.map((chat) =>
                          <li>
                            <span className="dpw-item-label">
                              <i className="fa fa-times"></i>
                              {chat.get('subject')}
                            </span>
                          </li>
                        ) || null}

                        {articles.size && articles.map((article) =>
                          <li>
                            <span className="dpw-item-label">
                              <i className="fa fa-times"></i>
                              {article.get('title')}
                            </span>
                          </li>
                        ) || null}
                      </ul>
                    </div>

                    <Select.Async
                      name="form-field-name"
                      loadOptions={this.getOptions}
                      clearable={false} />

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
