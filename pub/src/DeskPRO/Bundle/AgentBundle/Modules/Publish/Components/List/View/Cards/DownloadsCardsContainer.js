import React, {Component, PropTypes} from 'react';
import { ArticleCard } from './ArticleCard';
import { connect } from 'react-redux';
import { peopleSelector }
  from '../../../../Selectors/list';
import { toggleSelectedAction } from '../../../../Actions/publishMassActions';

@connect(state => {
  return ({
    people: peopleSelector(state),
    downloads: state.Publish.list.get('downloads'),
    selected: state.Publish.list.get('selected')
  });
})

export class DownloadsCardsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    people: PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { downloads, people, selected, dispatch } = this.props;
    const toggleSelected = (id) => () => dispatch(toggleSelectedAction(id));

    return (
      <div>
        {downloads.map((element, index) =>
            <ArticleCard key={index}
                         element={element}
                         toggleSelected={toggleSelected}
                         selected={selected.includes(element.id)}
                         author={people.get(element.person)}/>
        )}
      </div>
    );
  }
}