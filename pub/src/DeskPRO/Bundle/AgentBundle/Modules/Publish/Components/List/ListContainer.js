import React from 'react';
import { connect } from 'redux/react';
import { List } from './List';
import * as actions from '../../Actions/publishListActions';

@connect(state => state.PublishList)
export class ListContainer extends React.Component {

  render() {
    switch (this.props.content) {
      case 'articles':
        return (<List elements={this.props.articles} view={this.props.view} toggleView={this.toggleView.bind(this)} />);
      case 'news':
        return (<List elements={this.props.news} view={this.props.view} toggleView={this.toggleView.bind(this)} />);
      case 'downloads':
        return (<List elements={this.props.downloads} view={this.props.view} toggleView={this.toggleView.bind(this)} />);
      default:
        throw `Unknown list ${this.props.content}`;
    }
  }

  toggleView(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleView());
  }
}
