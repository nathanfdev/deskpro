import PropTypes from 'prop-types';
import React from 'react';
import { FilterControls } from './Controls/FilterControls';
import { portalHttp } from '../../Http/PortalHttp';
import { portalUrlCorrector } from '../../Http/PortalUrlCorrector';
import { FilterModel } from './FilterModel';
import { FilterOptions } from './FilterOptions';
import { ResultsPartial } from './ResultsPartial';

export class FeedbackFilter extends React.Component {

  static propTypes = {
    filter_data: PropTypes.object
  };

  constructor(props) {
    super(props);

    const available = new FilterOptions(props.filter_data.available);
    const filter = new FilterModel(props.filter_data.filter, available);

    this.state = {
      available,
      filter,
      doSpin:  true,
      partial: ''
    };
  }

  componentDidMount() {
    window.history.replaceState(this.state, null, window.history.location || window.location);
    this.onUpdateFilter(this.state.filter, true);
    window.addEventListener('popstate', (event) => {
      if (event.state === null || event.state.partial.length === 0) {
        return;
      }

      this.setState({
        filter:  new FilterModel(event.state.filter, this.state.available),
        partial: event.state.partial,
        doSpin:  event.state.doSpin
      });
    });
  }

  onUpdateFilter = (filterModel, initial = false) => {
    this.setState(
      {
        filter:  this.state.filter,
        doSpin:  true,
        partial: ''
      },
      () => {
        // we have to know the actual URL to put in the history.pushState, so
        // we call in and use PortalUrlCorrector directly...
        let url = filterModel.createUrl();
        const config = { url };
        portalUrlCorrector.request(config);
        url = config.url;

        portalHttp.sendGet(url).then((r) => {
          const state = {
            filter:  filterModel,
            partial: r.getData(),
            doSpin:  false
          };

          if (initial) {
            window.history.replaceState(state, null, url);
          } else {
            window.history.pushState(state, null, url);
          }

          this.setState(state);
        });
      }
    );
  };

  render() {
    return (
      <article className="feedback-filter-interactive">
        <FilterControls
          filterModel={this.state.filter}
          available={this.state.available}
          updateFilter={this.onUpdateFilter}
          doSpin={this.state.doSpin}
        />
        <ResultsPartial
          filterModel={this.state.filter}
          partial={this.state.partial}
          updateFilter={this.onUpdateFilter}
          doSpin={this.state.doSpin}
        />
      </article>
    );
  }
}
