import React from 'react';
import FilterControls from './Controls/FilterControls';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import PortalUrlCorrector from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlCorrector';
import FilterModel from './FilterModel';
import FilterOptions from './FilterOptions';
import ResultsPartial from './ResultsPartial';
import history from 'html5-history-api';

let location = window.history.location || window.location;

export default class FeedbackFilter extends React.Component {
  constructor(props) {
    super(props);
    let available = new FilterOptions(this.props.filter_data.available);
    let filter = new FilterModel(this.props.filter_data.filter, available);
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
        }
      );
    });
  }
  updateFilter(filter_model, initial = false) {
    this.setState({
      filter: this.state.filter,
      doSpin: true,
      partial: ''
    }, () => {
      // we have to know the actual URL to put in the history.pushState, so
      // we call in and use PortalUrlCorrector directly...
      let url = filter_model.createUrl();
      let config = {url:url};
      PortalUrlCorrector.request(config);
      url = config.url;

      portalHttp.sendGet(url).then(r => {
        let state = {
          filter: filter_model,
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
