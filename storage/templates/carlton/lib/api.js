import axios from 'axios'

const get = endpoint => axios.get(process.env.NEXT_PUBLIC_API_BASE_URL+'/site/'+endpoint,[
	    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }]);

export async function getHomeData()
{ 
  const { data: listing } = await get(process.env.NEXT_PUBLIC_WEBSITE_API_ID);

  return listing
}