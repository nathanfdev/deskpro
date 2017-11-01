import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { CardWidget } from './';
import { quickSearchAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/search';
import { editTask } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Actions/listActions';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import Select from 'react-select-plus';
import { connect } from 'react-redux';
import { addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import invariant from 'invariant';

export class LinkedItem extends CardWidget {

  static propTypes = {
    value: PropTypes.object,

    openBySingleClick: PropTypes.bool,
    onSetEditing:      PropTypes.func,
    onChange:          PropTypes.func,

    tickets:  PropTypes.object,
    chats:    PropTypes.object,
    articles: PropTypes.object,

    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    const empty = Immutable.fromJS({});
    this.state = {
      isOpen: props.isOpen,
      value:  this.convertLinkedItems(props.value)
    };
    this.fallback = {
      linked_tickets:  {},
      linked_articles: {},
      linked_chats:    {}
    };
    this.update = {
      linked_tickets:  [],
      linked_articles: [],
      linked_chats:    []
    };
  }

  convertLinkedItems(task) {
    return Immutable.Map({
      linked_tickets:  task.get('linked_tickets').toSet(),
      linked_articles: task.get('linked_articles').toSet(),
      linked_chats:    task.get('linked_chats').toSet()
    });
  }

  getItemTitle = (type, id) => {
    const item = this.props[type.substr(7)].get(id);
    switch (type) {
      case 'linked_tickets':
        return item ? item.get('subject') : this.fallback[type][id];
        break;
      case 'linked_articles':
        return item ? item.get('title') : this.fallback[type][id];
        break;
      case 'linked_chats':
        return item ? item.get('subject_line') : this.fallback[type][id];
        break;
    }
    return '???';
  };

  componentWillReceiveProps(props) {
    this.setState({
      value: this.convertLinkedItems(props.value)
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.isOpen !== state.isOpen || !Immutable.is(this.state.value, state.value);
  }

  deleteLinkedItem(type, value) {
    let items = this.state.value.get(type).delete(value);
    this.setState({
      value: this.state.value.set(type, items)
    });
  }

  componentDidUpdate(prevProps, prevState) {
    if (Immutable.is(this.state.value, prevState.value)) return;
    this.props.onChange && this.props.onChange(this.state.value);

    for (let [type, items] of Object.entries(this.update)) {
      switch (type) {
        case 'linked_tickets':
          this.props.dispatch(addToCollection('Ticket', 'all', items));
          break;
        case 'linked_articles':
          this.props.dispatch(addToCollection('Article', 'all', items));
          break;
        case 'linked_chats':
          this.props.dispatch(addToCollection('UserChat', 'all', items));
          break;
      }
    }

    this.update = {
      linked_tickets:  [],
      linked_articles: [],
      linked_chats:    []
    };
  }

  getOptions = (input, callback) => {
    const type = ['ticket', 'article', 'chat_conversation'];
    this.props.dispatch(quickSearchAction({ type, query: input })).then((res) => {
      invariant(res.data && res.data.data && res.data.data.grouped_results, 'Malformed QuickSearch response');

      let options = [];

      for (let group of res.data.data.grouped_results) {
        let option = { label: '', options: [] };
        options.push(option);

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
              value: 'linked_tickets.' + result.id,
              data:  result
            });
          } else if (group.type === 'chat') {
            option.options.push({
              label: result.subject,
              value: 'linked_chats.' + result.id,
              data:  result
            });
          } else if (group.type === 'article') {
            option.options.push({
              label: result.title,
              value: 'linked_articles.' + result.id,
              data:  result
            });
          }
        }
      }

      callback(null, { options });
    });
  };

  onSelectItem = (value, selectedOptions) => {
    let [type, id] = value.value.split('.');
    id = parseInt(id);
    this.fallback[type][id] = value.label;
    this.update[type].push(value.data);
    let items = this.state.value.get(type);
    this.setState({
      value: this.state.value.set(type, items.add(id))
    });
  };

  render() {
    const prop = { [this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen };
    const linked_tickets = this.state.value.get('linked_tickets')
      , linked_articles = this.state.value.get('linked_articles')
      , linked_chats = this.state.value.get('linked_chats')
      ;
    const { tickets, articles, chats } = this.props;
    const count = linked_tickets.size + linked_articles.size + linked_chats.size;
    let type, title = 'N/A';
    const getItemTitle = this.getItemTitle;

    if (1 === count) {
      switch (1) {
        case linked_tickets.size:
          title = 'Linked ticket: ' + getItemTitle('linked_tickets', linked_tickets.first());
          break;
        case linked_articles.size:
          title = 'Linked article: ' + getItemTitle('linked_articles', linked_articles.first());
          break;
        case linked_chats.size:
          title = 'Linked chat: ' + getItemTitle('linked_chats', linked_chats.first());
          break;
      }
    } else if (count > 1) {
      title = count + ' linked items';
    }

    return (
      <div>
        <div className="dpwd--card-line-item" {...prop}
          style={{ display: 'inline-block', position: 'relative', paddingLeft: 20, overflow: 'hidden', width: '100%' }}
        >
          <i className="fa fa-link" style={{ position: 'absolute', left: 2, top: 2 }} />
          <span title={title}>{title}</span>
        </div>

        <Positioned isOpen={this.state.isOpen}
          positionTarget={this}
          positionAt="right+5 top-23"
          collision="fit"
          zIndex={1002}
        >

          <ClickOut onClickOut={this.onClose}
            additionalNodes={[this.refs.button, '.fa-times']}
          >
            <div className="dpw-navigation-dropdown-panel">
              <div className="dpw-navigation-dropdown-panel-content">
                <div className="dpw-navigation-dropdown-panel-content-line">
                  <div className="dpw-navigation-dropdown-panel-content-full">

                    {count &&
                    <div className="dpw-label-pile">
                      <ul className="dpw-label-list">
                        {linked_tickets.map((id) => {
                          const item = tickets.get(id);
                          return (<li key={`linked_ticket.${id}`}>
                            <span className="dpw-item-label">
                              <i className="fa fa-times" style={{ cursor: 'pointer' }}
                                onClick={this.deleteLinkedItem.bind(this, 'linked_tickets', id)}
                              >
                              </i>
                              {item ? item.get('subject') : getItemTitle('linked_tickets', id)}
                            </span>
                          </li>);
                        })}
                        {linked_articles.map((id) => {
                          const item = articles.get(id);
                          return (<li key={`linked_article.${id}`}>
                            <span className="dpw-item-label">
                              <i className="fa fa-times" style={{ cursor: 'pointer' }}
                                onClick={this.deleteLinkedItem.bind(this, 'linked_articles', id)}
                              >
                              </i>
                              {item ? item.get('title') : getItemTitle('linked_articles', id)}
                            </span>
                          </li>);
                        })}
                        {linked_chats.map((id) => {
                          const item = chats.get(id);
                          return (<li key={`linked_chat.${id}`}>
                            <span className="dpw-item-label">
                              <i className="fa fa-times" style={{ cursor: 'pointer' }}
                                onClick={this.deleteLinkedItem.bind(this, 'linked_chats', id)}
                              >
                              </i>
                              {item ? item.get('subject_line') : getItemTitle('linked_chats', id)}
                            </span>
                          </li>);
                        })}
                      </ul>
                    </div> || null}

                    <Select.Async
                      name="form-field-name"
                      minimumInput={3}
                      loadOptions={this.getOptions}
                      placeholder="Link items..."
                      clearable={false}
                      onChange={this.onSelectItem}
                    />

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
