import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';

@connect(state => ({
  results: state.Application.search.get('results')
}))

export class QuickSearchResultsContainer extends React.Component {

  static propTypes = {
    results: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      results: props.results
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      results: props.results
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.results, state.results);
  }

  render() {
    const empty = Immutable.fromJS([]);

    const results = {
      tickets: this.state.results.get('ticket') || empty,
      articles: this.state.results.get('article') || empty,
      chats: this.state.results.get('chat_conversation') || empty,
      downloads: this.state.results.get('download') || empty,
      feedbacks: this.state.results.get('feedback') || empty,
      news: this.state.results.get('news') || empty,
      person: this.state.results.get('person') || empty,
      organization: this.state.results.get('organization') || empty
    };


    const { children } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      ...results
    });
  }
}
