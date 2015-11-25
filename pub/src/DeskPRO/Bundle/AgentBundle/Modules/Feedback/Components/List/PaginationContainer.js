import React, {Component, PropTypes} from 'react';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';
import { applyParams } from '../../Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    pagination: state.Feedback.list.get('pagination')
  });
})

export class PaginationContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    pagination: PropTypes.object.isRequired
  };

  handlePageClick(page) {
    this.props.dispatch(applyParams({ page: page }));
  }

  render() {
    const {pagination} = this.props;
    return (
      <PaginationBoxView breakLabel={<li className="break"><a href="">...</a></li>}
                         pageNum={pagination.total_pages}
                         currentPage={pagination.current_page}
                         marginPagesDisplayed="2"
                         pageRangeDisplayed="2"
                         clickCallback={this.handlePageClick.bind(this)}
                         containerClassName="pages-list"
                         subContainerClassName="pages-list"
                         activeClassName="current-page"/>
    );
  }
}