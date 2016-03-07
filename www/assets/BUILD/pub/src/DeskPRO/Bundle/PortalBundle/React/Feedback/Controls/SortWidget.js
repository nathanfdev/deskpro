import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { PortalSimpleSelectBox } from 'DeskPRO/Bundle/PortalBundle/React/Form/PortalSimpleSelectBox';
import _ from 'lodash';

export class SortWidget extends React.Component {

  static propTypes = {
    setSort: PropTypes.func,
    filter: PropTypes.object
  };

  onChangeSort = option => {
    this.props.setSort(option.id);
  };

  render() {
    const { filter } = this.props;

    const arrowDown = ' (desc)';
    const arrowUp = ' (asc)';

    const sorts = {
      'date-desc': portalPhrases.get('portal.general.prop_date') + arrowDown,
      'date-asc': portalPhrases.get('portal.general.prop_date') + arrowUp,
      'most-views-desc': portalPhrases.get('portal.general.prop_views') + arrowDown,
      'most-views-asc': portalPhrases.get('portal.general.prop_views') + arrowUp,
      'highest-rating-desc': portalPhrases.get('portal.general.prop_rating') + arrowDown,
      'highest-rating-asc': portalPhrases.get('portal.general.prop_rating') + arrowUp,
      'most-popular-desc': portalPhrases.get('portal.general.prop_popularity') + arrowDown,
      'most-popular-asc': portalPhrases.get('portal.general.prop_popularity') + arrowUp,
      'most-discussed-desc': portalPhrases.get('portal.general.prop_comments') + arrowDown,
      'most-discussed-asc': portalPhrases.get('portal.general.prop_comments') + arrowUp
    };

    const selectedSort = {id: filter.sort + '-' + filter.sort_direction, title: "Sort"};
    if (sorts[selectedSort.id]) {
      selectedSort.title = sorts[selectedSort.id];
    }

    const widgetOptions = {
      widgetClassName: ['small', 'borderless', 'right']
    };
    const options = _.map(sorts, (title, id) => {
      return {
        id: id,
        title: title
      };
    });

    return (
      <div style={{float:"right", minWidth: '120px'}}>
        <PortalSimpleSelectBox widgetOptions={widgetOptions} options={options} value={selectedSort} onChange={this.onChangeSort} />
      </div>
    );
  }
}
