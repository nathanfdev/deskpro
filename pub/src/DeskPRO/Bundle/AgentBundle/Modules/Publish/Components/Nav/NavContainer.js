import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/publishNavActions'
import { Nav } from './Nav';

@connect(state => state.PublishNav)
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
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
