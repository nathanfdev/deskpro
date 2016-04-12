import React from 'react';
import ReactDOM from 'react-dom';
import _ from 'lodash';
import $ from 'jquery';
import { portalUrlGenerator } from '../../Http/PortalUrlGenerator';
import { FeedbackVoteWidget } from '../../PageWidget/FeedbackVoteWidget';

export class ResultsPartial extends React.Component {

  render() {
    let html = this.props.partial;
    if (html.length === 0) {
      return (
        <div className="paged-results centered" ref="results">
          <img
            style={{display: this.props.doSpin ? 'table' : 'none', margin: '0 auto', height: '70px', width: '70px'}}
            src={ portalUrlGenerator.getSpinnerPath() } />
        </div>
      );
    }
    return (
      <div className="paged-results" ref="results" dangerouslySetInnerHTML={{ __html: html }} />
    );
  }

  componentDidMount() {
    this.setEvents();
  }

  updatePage(page) {
    let new_filter = this.props.filterModel;
    new_filter.page = page;
    this.props.updateFilter(new_filter);
  }

  setStatusCategory(id) {
    let new_filter = this.props.filterModel;
    new_filter.reset();
    new_filter.setStatusCategory(id);
    this.props.updateFilter(new_filter);
  }

  setType(id) {
    let new_filter = this.props.filterModel;
    new_filter.reset();
    new_filter.setType(id);
    this.props.updateFilter(new_filter);
  }

  componentDidUpdate() {
    this.setEvents();
  }

  setEvents() {
    let self = this;
    let results = $(ReactDOM.findDOMNode(this.refs.results));

    // process pager
    results.find('.deskpro-pager a').each(function () {
      $(this).click(function (e) {
        e.preventDefault();
        let uri = $(this).attr('href');
        let getparam = function get(n) {
          let half = uri.split(n + '=')[1];
          return half !== undefined ? decodeURIComponent(half.split('&')[0]) : null;
        };
        self.updatePage(getparam('page'));

        return false;
      });
    });

    // add events to "I Agree"
    results.find('.feedback-item-controls a.i-agree').each(function () {
      const $el = $(this);
      const w = new FeedbackVoteWidget($el);
      w.render();
    });

    // add events to status category links
    results.find('.feedback-item-content .feedback-status a').each(function () {
      let $statusCategoryLink = $(this);
      $statusCategoryLink.click(function (e) {
        e.preventDefault();
        self.setStatusCategory($statusCategoryLink.data('id'));
        return false;
      });
    });

    // add events to type (categories) links
    results.find('a.feedback-category').each(function () {
      let $typeLink = $(this);
      $typeLink.click(function (e) {
        e.preventDefault();
        self.setType($typeLink.data('id'));
        return false;
      });
    });
  }
}
