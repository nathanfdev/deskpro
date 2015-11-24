import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import {PaginationListView} from './PaginationListView';

export class PaginationBoxView extends Component {

  static propTypes = {
    pageNum: PropTypes.number.isRequired,
    pageRangeDisplayed: PropTypes.number.isRequired,
    marginPagesDisplayed: PropTypes.number.isRequired,
    previousLabel: PropTypes.node,
    nextLabel: PropTypes.node,
    breakLabel: PropTypes.node,
    clickCallback: PropTypes.func,
    initialSelected: PropTypes.number,
    forceSelected: PropTypes.number,
    containerClassName: PropTypes.string,
    subContainerClassName: PropTypes.string,
    pageClassName: PropTypes.string,
    pageLinkClassName: PropTypes.string,
    activeClassName: PropTypes.string,
    previousClassName: PropTypes.string,
    nextClassName: PropTypes.string,
    previousLinkClassName: PropTypes.string,
    nextLinkClassName: PropTypes.string,
    disabledClassName: PropTypes.string
  };

  static defaultProps = {
    pageNum: 10,
    pageRangeDisplayed: 2,
    marginPagesDisplayed: 3,
    activeClassName: 'selected',
    previousClassName: 'previous',
    nextClassName: 'next',
    previousLabel: 'Previous',
    nextLabel: 'Next',
    breakLabel: '...',
    disabledClassName: 'disabled'
  };

  constructor(props) {
    super(props);

    this.state = {
      selected: props.initialSelected ? props.initialSelected : 0
    };
  }

  componentWillReceiveProps(nextProps) {
    if (typeof nextProps.forceSelected !== 'undefined' && nextProps.forceSelected !== this.state.selected) {
      this.setState({ selected: nextProps.forceSelected });
    }
  }

  handlePageSelected(selected, event) {
    event.preventDefault();

    if (this.state.selected === selected) {
      // Display the dropdown list
      this.setState({ dropdown: !this.state.dropdown });
      return;
    }

    this.setState({
      selected: selected,
      dropdown: false
    });

    if (typeof(this.props.clickCallback) !== 'undefined' &&
      typeof(this.props.clickCallback) === 'function') {
      this.props.clickCallback({ selected: selected });
    }
  }

  handlePreviousPage(event) {
    event.preventDefault();
    if (this.state.selected > 0) {
      this.handlePageSelected(this.state.selected - 1, event);
    }
  }

  handleNextPage(event) {
    event.preventDefault();
    if (this.state.selected < this.props.pageNum - 1) {
      this.handlePageSelected(this.state.selected + 1, event);
    }
  }

  render() {
    const disabled = this.props.disabledClassName;

    const previousClasses = classNames(this.props.previousClassName,
      { [disabled]: this.state.selected === 0 });

    const nextClasses = classNames(this.props.nextClassName,
      { [disabled]: this.state.selected === this.props.pageNum - 1 });
    return (
      <ul className={this.props.containerClassName}>
        <li onClick={this.handlePreviousPage} className={previousClasses}>
          <a href="" className={this.props.previousLinkClassName}><i className="fa fa-caret-left"></i></a>
        </li>

        <PaginationListView
          onPageSelected={this.handlePageSelected}
          selected={this.state.selected}
          pageNum={this.props.pageNum}
          pageRangeDisplayed={this.props.pageRangeDisplayed}
          marginPagesDisplayed={this.props.marginPagesDisplayed}
          breakLabel={this.props.breakLabel}
          subContainerClassName={this.props.subContainerClassName}
          pageClassName={this.props.pageClassName}
          pageLinkClassName={this.props.pageLinkClassName}
          activeClassName={this.props.activeClassName}
          disabledClassName={this.props.disabledClassName}
          dropdown={this.state.dropdown}/>

        <li onClick={this.handleNextPage} className={nextClasses}>
          <a href="" className={this.props.nextLinkClassName}><i className="fa fa-caret-right"></i></a>
        </li>
      </ul>
    );
  }

}
