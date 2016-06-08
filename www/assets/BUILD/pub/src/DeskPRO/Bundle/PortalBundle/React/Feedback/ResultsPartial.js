import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { portalUrlGenerator } from '../../Http/PortalUrlGenerator';
import { FeedbackVoteWidget } from '../../PageWidget/FeedbackVoteWidget';
import classNames from 'classnames';

export class ResultsPartial extends React.Component {

  static propTypes = {
    partial:      PropTypes.string,
    doSpin:       PropTypes.bool,
    filterModel:  PropTypes.object,
    updateFilter: PropTypes.func
  };

  componentDidMount() {
    this.setEvents();
  }

  componentDidUpdate() {
    this.setEvents();
  }

  setStatusCategory(id) {
    const { filterModel, updateFilter } = this.props;

    filterModel.reset();
    filterModel.setStatusCategory(id);

    updateFilter(filterModel);
  }

  setType(id) {
    const { filterModel, updateFilter } = this.props;

    filterModel.reset();
    filterModel.setType(id);

    updateFilter(filterModel);
  }

  setEvents() {
    const $results = $(ReactDOM.findDOMNode(this.refs.results));

    // process pager
    $('.deskpro-pager a', $results).each((i, item) => {
      $(item).on('click', event => {
        event.preventDefault();

        const uri = $(item).attr('href');
        const half = uri.split('page=')[1];
        const page = half !== undefined ? decodeURIComponent(half.split('&')[0]) : null;

        this.updatePage(page);
      });
    });

    // add events to "I Agree"
    $('.feedback-item-controls a.i-agree', $results).each((i, item) => {
      const w = new FeedbackVoteWidget($(item));
      w.render();
    });

    // add events to status category links
    $('.feedback-item-content .feedback-status a', $results).each((i, item) => {
      $(item).on('click', event => {
        event.preventDefault();
        this.setStatusCategory($(item).data('id'));
      });
    });

    // add events to type (categories) links
    $('a.feedback-category', $results).each((i, item) => {
      $(item).on('click', event => {
        event.preventDefault();
        this.setType($(item).data('id'));
      });
    });
  }

  updatePage(page) {
    const { filterModel, updateFilter } = this.props;

    filterModel.page = page;
    updateFilter(filterModel);
  }

  render() {
    const { partial, doSpin } = this.props;

    if (partial.length === 0) {
      return (
        <div className="paged-results centered" ref="results">
          <img
            src={portalUrlGenerator.getSpinnerPath()}
            style={{
              display: doSpin ? 'table' : 'none',
              margin:  '0 auto',
              height:  '70px',
              width:   '70px'
            }}
          />
        </div>
      );
    }

    return (
      <div
        className={classNames('paged-results', { centered: partial.indexOf('no-data') !== -1 })}
        ref="results"
        dangerouslySetInnerHTML={{ __html: partial }}
      ></div>
    );
  }
}
