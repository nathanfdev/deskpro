import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/publishNavActions'
import { Nav } from './Nav';

@connect(state => state.PublishNav)
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadArticlesCounts(this.props.articles.grouped_by));
    this.props.dispatch(actions.loadNewsCounts(this.props.news.grouped_by));
    this.props.dispatch(actions.loadDownloadsCounts(this.props.downloads.grouped_by));
    this.props.dispatch(actions.loadCategories());
  }

  render() {
    return (
      <Nav
        articles={this.props.articles}
        news={this.props.news}
        downloads={this.props.downloads}
        categories={this.props.categories}
      />
    );
  }
}
