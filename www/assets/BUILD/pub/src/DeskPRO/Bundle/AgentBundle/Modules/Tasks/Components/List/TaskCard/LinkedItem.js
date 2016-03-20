import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { CardWidget, SearchResults } from './index';
import { quickSearchAction, quickSearchResetAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/search';
import { QuickSearchResultsContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/QuickSearchResultsContainer';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

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

  onOpen = () => {
    if (this.state.isOpen) return;
    this.setState({isOpen: true});
    this.props.onSetEditing && this.props.onSetEditing(true);
    this.props.dispatch(quickSearchResetAction);
  };

  doSearch = (val) => {
    if (this._isSearching) return;
    if (this._recentSearch === val) return;
    this._recentSearch = val;
    const type = ['ticket', 'article', 'chat_conversation'];
    this.props.dispatch(quickSearchAction({type: type, query: val}));
  };

  onChange = (event) => {
    const val = event.target.value;
    this._search = val;
    this.doSearch(val);
  };

  onStartSearch = () => {
    this._isSearching = true;
  };

  onStopSearch = () => {
    this._isSearching = false;
    this.doSearch(this._search);
  };

  render() {
    const prop = {[this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen};
    const v = this.state.value;
    const count = v.get('linked_tickets').size + v.get('linked_chats').size + v.get('linked_articles').size;
    let title = 'N/A';

    if (1 === count) {
      let id;
      // todo find or select from collections?
      title = 'Not implemented yet';
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

                    <input type="text" onChange={this.onChange} />

                    <div style={{height: 50, overflowY: 'scroll', position: 'relative'}}>
                      <LoadIndicator selector={state => state.Application.search.get('searching')}
                                     onStartLoading={this.onStartSearch}
                                     onStopLoading={this.onStopSearch}>
                        <QuickSearchResultsContainer>
                          <SearchResults />
                        </QuickSearchResultsContainer>
                      </LoadIndicator>
                    </div>

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
