import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { CardWidget } from './';
import { quickSearchAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/search';
import { editTask } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Actions/listActions';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import Select from 'react-select-plus';
import { connect } from 'react-redux';

@connect()

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
      value: this.convertLinkedItems(props.value)
    };
  }

  convertLinkedItems(task) {
    let items = Immutable.Map();

    for (let type of ['ticket', 'article', 'chat']) {
      for (let item of task.get(`linked_${type}s`)) {
        items = items.set(type + '.' + item.get('id'), item);
      }
    }

    return items;
  }

  componentWillReceiveProps(props) {
    const empty = Immutable.fromJS({});
    this.setState({
      value: this.convertLinkedItems(props.value)
    });

    if (undefined !== props.isOpen) {
      this.setState({isOpen: props.isOpen});
    }
  }

  shouldComponentUpdate(props, state) {
    return this.state.isOpen !== state.isOpen || this.state.value !== state.value
      || !Immutable.is(this.state.value, state.value);
  }

  deleteLinkedItem(id) {
    this.setState({
      value: this.state.value.remove(id)
    });
  }

  componentDidUpdate(prevProps, prevState) {
    if (Immutable.is(this.state.value, prevState.value)) return;
    if (!this.props.onChange) return;

    let data = {
      linked_tickets: [],
      linked_articles: [],
      linked_chats: []
    };

    for (let [k, item] of this.state.value) {
      const type = k.split('.')[0];
      switch (type) {
        case 'ticket':
          data.linked_tickets.push(item);
          break;
        case 'article':
          data.linked_articles.push(item);
          break;
        case 'chat':
          data.linked_chats.push(item);
          break;
      }
    }

    this.props.onChange(Immutable.fromJS(data));
  }

  getOptions = (input, callback) => {
    const type = ['ticket', 'article', 'chat_conversation'];
    this.props.dispatch(quickSearchAction({type: type, query: input})).then((res) => {

      let options = [];
      if (!res.data || !res.data.grouped_results) {
        return callback(null, {options: options});
      }

      for (let group of res.data.grouped_results) {
        let option = {label: '', options: []};
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
              value: 'ticket.' + result.id
            });
          } else  if (group.type === 'chat') {
            option.options.push({
              label: result.subject,
              value: 'chat.' + result.id
            });
          } else  if (group.type === 'article') {
            option.options.push({
              label: result.title,
              value: 'article.' + result.id
            });
          }
        }
      }

      callback(null, {options: options});
    });
  };

  onSelectItem = (value, selectedOptions) => {
    const [type, id] = value.value.split('.');
    const obj = {
      id: id,
      title: value.label
    };
    this.setState({
      value: this.state.value.set(value.value, Immutable.fromJS(obj))
    });
  };

  render() {
    const prop = {[this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen};
    const items = this.state.value;
    const count = items.size;
    let title = 'N/A';

    if (1 === count) {
      const item = items.first();
      const key = items.keySeq().first();
      const type = key.split('.')[0];
      title = `Linked ${type}: ${item.get('title')}`;
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
                    additionalNodes={[this.refs.button, '.fa-times']}>
            <div className="dpw-navigation-dropdown-panel">
              <div className="dpw-navigation-dropdown-panel-content">
                <div className="dpw-navigation-dropdown-panel-content-line">
                  <div className="dpw-navigation-dropdown-panel-content-full">

                    {items.size &&
                    <div className="dpw-label-pile">
                      <ul className="dpw-label-list">
                        {items.map((item, key) =>
                          <li key={key}>
                            <span className="dpw-item-label">
                              <i className="fa fa-times" style={{cursor: 'pointer'}}
                                 onClick={this.deleteLinkedItem.bind(this, key)}>
                              </i>
                              {item.get('title')}
                            </span>
                          </li>
                        )}
                      </ul>
                    </div> || null}

                    <Select.Async
                      name="form-field-name"
                      minimumInput={3}
                      loadOptions={this.getOptions}
                      placeholder="Link items..."
                      clearable={false}
                      onChange={this.onSelectItem} />

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
