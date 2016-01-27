import React, { PropTypes } from 'react';
import FilterControls from './Controls/FilterControls';
import { portalHttp } from '../../Http/PortalHttp';
import { portalUrlCorrector } from '../../Http/PortalUrlCorrector';
import FilterModel from './FilterModel';
import FilterOptions from './FilterOptions';
import ResultsPartial from './ResultsPartial';
import history from 'html5-history-api';

export default class FeedbackFilter extends React.Component {

  static propTypes = {
    filter_data: PropTypes.object
  };

  constructor(props) {
    super(props);

    const available = new FilterOptions(props.filter_data.available);
    const filter = new FilterModel(props.filter_data.filter, available);

    this.state = {
      available: available,
      filter: filter,
      doSpin: true,
      partial: ''
    };
  }

  componentDidMount() {
    history.replaceState(this.state, null, window.history.location || window.location);
    this.updateFilter(this.state.filter, true);
    window.addEventListener('popstate', (e) => {
      if (e.state === null) {
        return;
      }
      if (e.state.partial.length === 0) {
        return;
      }

      this.setState({
        filter: new FilterModel(e.state.filter, this.state.available),
        partial: e.state.partial,
        doSpin: e.state.doSpin
      });
    });
  }

  updateFilter(filterModel, initial = false) {
    this.setState({
      filter: this.state.filter,
      doSpin: true,
      partial: ''
    }, () => {
      // we have to know the actual URL to put in the history.pushState, so
      // we call in and use PortalUrlCorrector directly...
      let url = filterModel.createUrl();
      const config = {url: url};
      portalUrlCorrector.request(config);
      url = config.url;

      portalHttp.sendGet(url).then(r => {
        const state = {
          filter: filterModel,
          partial: r.getData(),
          doSpin: false
        };

        if (initial) {
          history.replaceState(state, null, url);
        } else {
          history.pushState(state, null, url);
        }

        this.setState(state);
      });
    });
  }

  render() {
    return (
      <article className="feedback-filter-interactive">
        <FilterControls filterModel={this.state.filter}
                        available={this.state.available}
                        updateFilter={this.updateFilter.bind(this)}
                        doSpin={this.state.doSpin}/>
        <ResultsPartial filterModel={this.state.filter}
                         partial={this.state.partial}
                         updateFilter={this.updateFilter.bind(this)}
                         doSpin={this.state.doSpin} />
      </article>
    );
  }
}
