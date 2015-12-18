import React, {Component, PropTypes} from 'react';
import { ArticleCard } from './ArticleCard';
import { connect } from 'react-redux';
import { peopleSelector }
  from '../../../../Selectors/list';

@connect(state => {
  return ({
    people: peopleSelector(state),
    downloads: state.Publish.list.get('downloads')
  });
})

export class DownloadsCardsContainer extends Component {

  static propTypes = {
    people: PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired
  };

  render() {
    const { downloads, people } = this.props;

    return (
      <div>
        {downloads.map((element, index) =>
            <ArticleCard key={index}
                         element={element}
                         author={people.get(element.person)}/>
        )}
      </div>
    );
  }
}